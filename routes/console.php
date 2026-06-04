<?php

use App\Models\SensorReading;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mqtt:listen-sensors {--once : Stop after saving the first valid MQTT message}', function () {
    $host = config('app.mqtt_host');
    $port = (int) config('app.mqtt_port');
    $topic = config('app.mqtt_sensor_topic');
    $clientId = config('app.mqtt_client_id').'-'.getmypid();
    $userId = (int) config('app.arduino_user_id');
    $username = filled(config('app.mqtt_username')) ? config('app.mqtt_username') : null;
    $password = filled(config('app.mqtt_password')) ? config('app.mqtt_password') : null;

    $this->info("Connecting to MQTT broker {$host}:{$port}");
    $this->info("Subscribing to {$topic}");

    while (true) {
        $mqtt = new MqttClient($host, $port, $clientId);
        $command = $this;

        try {
            $settings = (new ConnectionSettings)
                ->setUsername($username)
                ->setPassword($password)
                ->setConnectTimeout(5)
                ->setSocketTimeout(5)
                ->setKeepAliveInterval(10);

            $mqtt->connect($settings, true);

            $mqtt->subscribe($topic, function (string $topic, string $message) use ($command, $mqtt, $userId) {
                $payload = json_decode($message, true);

                if (!is_array($payload)) {
                    $command->warn("Invalid JSON on {$topic}: {$message}");
                    return;
                }

                $pumpOn = filter_var($payload['pumpOn'] ?? false, FILTER_VALIDATE_BOOLEAN);

                $reading = SensorReading::create([
                    'user_id' => $userId,
                    'ph' => (float) ($payload['ph'] ?? 0),
                    'nutrient' => (float) ($payload['tds'] ?? $payload['nutrient'] ?? 0),
                    'turbidity' => (float) ($payload['turbidityPercent'] ?? $payload['turbidity'] ?? 0),
                    'pump_pwm' => (float) ($payload['pump_pwm'] ?? ($pumpOn ? 100 : 0)),
                    'humidity' => (float) ($payload['humidity'] ?? 0),
                    'status' => 'normal',
                ]);

                $command->info(sprintf(
                    'Saved sensor reading #%d: pH %.2f, TDS %.2f, turbidity %.2f, pump %.1f%%',
                    $reading->id,
                    $reading->ph,
                    $reading->nutrient,
                    $reading->turbidity,
                    $reading->pump_pwm
                ));

                if ($command->option('once')) {
                    $mqtt->interrupt();
                }
            }, 0);

            $mqtt->loop(true);
            $mqtt->disconnect();

            if ($this->option('once')) {
                return self::SUCCESS;
            }
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            try {
                $mqtt->disconnect();
            } catch (\Throwable) {
                //
            }

            if ($this->option('once')) {
                return self::FAILURE;
            }

            $this->warn('Reconnecting in 5 seconds...');
            sleep(5);
        }
    }
})->purpose('Subscribe to sensor MQTT data and save it into sensor_readings');

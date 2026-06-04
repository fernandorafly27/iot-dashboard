<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sensor_readings', function (Blueprint $table) {
            if (!Schema::hasColumn('sensor_readings', 'user_id')) {
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('sensor_readings', 'ph')) {
                $table->float('ph')->default(0);
            }
            if (!Schema::hasColumn('sensor_readings', 'nutrient')) {
                $table->float('nutrient')->default(0);
            }
            if (!Schema::hasColumn('sensor_readings', 'turbidity')) {
                $table->float('turbidity')->default(0);
            }
            if (!Schema::hasColumn('sensor_readings', 'pump_pwm')) {
                $table->float('pump_pwm')->default(0);
            }
            if (!Schema::hasColumn('sensor_readings', 'humidity')) {
                $table->float('humidity')->default(0);
            }
            if (!Schema::hasColumn('sensor_readings', 'status')) {
                $table->string('status')->default('normal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sensor_readings', function (Blueprint $table) {
            $table->dropColumn(['user_id','ph','nutrient','turbidity','pump_pwm','humidity','status']);
        });
    }
};

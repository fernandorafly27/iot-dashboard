<?php

use App\Models\SensorReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/sensor-readings', function (Request $request) {
    if ($request->header('X-API-Key') !== config('app.arduino_api_key')) {
        abort(401, 'Invalid API key.');
    }

    $data = $request->validate([
        'ph' => ['required', 'numeric', 'between:0,14'],
        'nutrient' => ['required', 'numeric', 'min:0'],
        'turbidity' => ['required', 'numeric', 'min:0'],
        'pump_pwm' => ['required', 'numeric', 'between:0,100'],
        'humidity' => ['required', 'numeric', 'between:0,100'],
    ]);

    $reading = SensorReading::create([
        'user_id' => config('app.arduino_user_id'),
        'ph' => $data['ph'],
        'nutrient' => $data['nutrient'],
        'turbidity' => $data['turbidity'],
        'pump_pwm' => $data['pump_pwm'],
        'humidity' => $data['humidity'],
        'status' => 'normal',
    ]);

    return response()->json([
        'ok' => true,
        'id' => $reading->id,
    ], 201);
});

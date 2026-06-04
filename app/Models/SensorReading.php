<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $fillable = [
        'user_id',
        'ph',
        'nutrient',
        'turbidity',
        'pump_pwm',
        'humidity',
        'status',
    ];

    protected $casts = [
        'ph'       => 'float',
        'nutrient' => 'float',
        'turbidity'=> 'float',
        'pump_pwm' => 'float',
        'humidity' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

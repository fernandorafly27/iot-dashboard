<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = [
        "name",
        "description",
        "code",
        "category",
        "duration",
        "status",
        "user_id",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
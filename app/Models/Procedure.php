<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    use HasFactory; 
    
    protected $guarded=['id'];
    protected $casts = [
        'is_operation' => 'boolean',
    ];

    protected static function booted()
{
    static::creating(function ($procedure) {
        if (is_null($procedure->price_foreign)) {
            $procedure->price_foreign = $procedure->price_per_day;
        }
    });
}
}

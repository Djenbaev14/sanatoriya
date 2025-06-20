<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    public function beds()
    {
        return $this->hasMany(Bed::class);
    }
    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }

    // Bo'sh koygalar sonini olish
    public function getAvailableBedsCountAttribute()
    {
        return $this->beds()->availableBeds()->count();
    }

    // Jami koygalar soni
    public function getTotalBedsCountAttribute()
    {
        return $this->beds()->count();
    }
}

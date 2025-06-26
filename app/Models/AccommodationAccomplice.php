<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccommodationAccomplice extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }
    public function patient(){
        return $this->belongsTo(Patient::class);
    }
    public function ward(){
        return $this->belongsTo(Ward::class);
    }
    public function bed(){
        return $this->belongsTo(Bed::class);
    }
    public function tariff(){
        return $this->belongsTo(Tariff::class);
    }

}

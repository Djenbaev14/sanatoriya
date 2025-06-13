<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalBed extends Model
{
    use HasFactory;

    protected $guarded=['id'];

    public function medicalHistory()
    {
        return $this->belongsTo(MedicalHistory::class);
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }
    
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use HasFactory;
    
    protected $guarded=['id'];
    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }
    public function medicalBeds()
    {
        return $this->hasMany(MedicalBed::class);
    }
    
    public function currentOccupancy()
    {
        return $this->hasOne(MedicalBed::class)
                    ->join('medical_histories', 'medical_beds.medical_history_id', '=', 'medical_histories.id')
                    ->whereNull('medical_histories.discharge_date');
    }
    
}

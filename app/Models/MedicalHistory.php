<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalHistory extends Model
{
    use HasFactory;
    protected $guarded=['id'];

    public function patient(){
        return $this->belongsTo(Patient::class);
    }
    public function assignedProcedures(){
        return $this->hasMany(AssignedProcedure::class);
    }
    public function labTestHistories(){
        return $this->hasMany(LabTestHistory::class);
    }
    public function medicalMeal(){
        return $this->hasOne(MedicalMeal::class);
    }
    public function medicalBed(){
        return $this->hasOne(MedicalBed::class);
    }
}

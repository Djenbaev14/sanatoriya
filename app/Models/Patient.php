<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    public function region(){
        return $this->belongsTo(Region::class);
    }
    public function district(){
        return $this->belongsTo(District::class);
    }
    public function medicalHistories(){
        return $this->hasMany(MedicalHistory::class);
    }
    public function assignedProcedures(){
        return $this->hasMany(AssignedProcedure::class);
    }
    public function labTestHistories(){
        return $this->hasMany(LabTestHistory::class);
    }
    
}

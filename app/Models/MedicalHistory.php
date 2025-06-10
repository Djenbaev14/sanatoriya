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
    public function assigned_procedures(){
        return $this->hasMany(AssignedProcedure::class);
    }
    public function lab_test_histories(){
        return $this->hasMany(LabTestHistory::class);
    }
}

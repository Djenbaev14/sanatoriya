<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentInspection extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    // medicalHistory bilan bog'liq bo'lgan DepartmentInspection
    public function medicalHistory()
    {
        return $this->belongsTo(MedicalHistory::class, 'medical_history_id');
    }
    // assignedDoctor bilan bog'liq bo'lgan DepartmentInspection
    public function assignedDoctor()
    {
        return $this->belongsTo(User::class, 'assigned_doctor_id');
    }
    
}

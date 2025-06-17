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
    public function doctor(){
        return $this->belongsTo(User::class);
    }
    public function assignedProcedures(){
        return $this->hasMany(AssignedProcedure::class);
    }
    public function returnedProcedures(){
        return $this->hasMany(ReturnedProcedure::class);
    }
    public function labTestHistory(){
        return $this->hasOne(LabTestHistory::class);
    }
    public function medicalInspection()
    {
        return $this->hasOne(\App\Models\MedicalInspection::class, 'medical_history_id');
    }
    
    public function medicalMeal(){
        return $this->hasOne(MedicalMeal::class);
    }
    public function medicalBed(){
        return $this->hasOne(MedicalBed::class);
    }
    public function getTotalCost()
    {
        return $this->inspectionDetails()
            ->sum(\DB::raw('price'));
    }
    // public function getTotalPaidAmount()
    // {
    //     return $this->payments()->sum('amount');
    // }
    
}

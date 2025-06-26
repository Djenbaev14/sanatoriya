<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MedicalHistory extends Model
{
    use HasFactory,LogsActivity,SoftDeletes;
    protected $guarded=['id'];

    protected $casts = [
        'disability_types' => 'array',
    ];
    

    protected static $logName = 'medical_history';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('medical_history');
    }
    public function departmentInspection(){
        return $this->hasOne(DepartmentInspection::class);
    }
    public function BedMealstatusPayment(){
        return $this->belongsTo(StatusPayment::class,'status_payment_id');
    }
    public function createdBy(){
        return $this->belongsTo(User::class,'created_id');
    }
    public function patient(){
        return $this->belongsTo(Patient::class);
    }
    public function doctor(){
        return $this->belongsTo(User::class);
    }
    public function assignedProcedure(){
        return $this->hasOne(AssignedProcedure::class);
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
    public function accommodation()
    {
        return $this->hasOne(\App\Models\Accommodation::class, 'medical_history_id');
    }
    public function getMedicalInspectionAttribute(){
        return $this->medicalInspection()->first();
    }
    

    public function payments(){
        return $this->hasMany(Payment::class);
    }
    public function calculateTotalCost()
    {
        $proceduresCost = $this->calculateProceduresCost();
        $bedCost = $this->calculateBedCost();
        $mealCost = $this->calculateMealCost();

        return [
            'procedures_cost' => $proceduresCost,
            'bed_cost' => $bedCost,
            'meal_cost' => $mealCost,
            'total_cost' => $proceduresCost + $bedCost + $mealCost
        ];
    }
    
    
    public function calculateDays()
    {
            $admissionDate = \Carbon\Carbon::parse($this->admission_date);
            $dischargeDate = \Carbon\Carbon::parse($this->discharge_date);
                
            $days = $admissionDate->diffInDays($dischargeDate) + 1;
            // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
            if ($admissionDate->format('H:i') > '12:00' && $days > 0) {
                $days -= 1;
            }
            
            return $days;
    }
    public function calculateMealCost()
    {
        try {
            if (!$this->admission_date) {
                return 0;
            }

            $medicalMeal = $this->medicalMeal()->with('mealType')->first();
            if (!$medicalMeal || !$medicalMeal->mealType) {
                return 0;
            }
            


            $admissionDate = \Carbon\Carbon::parse($this->admission_date);
            $dischargeDate = $this->discharge_date 
                ? \Carbon\Carbon::parse($this->discharge_date)
                : \Carbon\Carbon::now();
                
            $days = $admissionDate->diffInDays($dischargeDate) + 1;
            // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
            if ($admissionDate->format('H:i') > '12:00' && $days > 0) {
                $days -= 1;
            }
            
            $days = max($days, 1);

            $totalCost = $medicalMeal->mealType->daily_price * $days;
            
            return $totalCost;
            
        } catch (\Exception $e) {
            \Log::error('calculateMealCost error: ' . $e->getMessage());
            return 0;
        }
    }
    public function calculateProceduresCost()
    {
        return $this->procedureDetails()
            ->sum(\DB::raw('price * sessions'));
    }
    public function calculateBedCost()
    {
        if (!$this->admission_date) {
            return 0;
        }

        $medicalBed = $this->medicalBed()->with('tariff')->first();

        if (!$medicalBed || !$medicalBed->tariff) {
            return 0;
        }

            $admissionDate = \Carbon\Carbon::parse($this->admission_date);
            $dischargeDate = $this->discharge_date 
                ? \Carbon\Carbon::parse($this->discharge_date)
                : \Carbon\Carbon::now();
                
            $days = $admissionDate->diffInDays($dischargeDate) + 1;
            // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
            if ($admissionDate->format('H:i') > '12:00' && $days > 0) {
                $days -= 1;
            }
            
            $days = max($days, 1);

        return $medicalBed->tariff->daily_price * $days;
    }
    
    public function getTotalCost()
    {
        return $this->calculateBedCost()+$this->calculateMealCost();
    }
    public function getTotalPaidBedAndMealAmount()
    {
        return $this->payments()->sum('amount');
    }
    
}

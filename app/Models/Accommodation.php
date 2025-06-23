<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public function patient(){
        return $this->belongsTo(Patient::class);
    }
    public function statusPayment(){
        return $this->belongsTo(StatusPayment::class);
    }
    public function payments(){
        return $this->hasMany(Payment::class);
    }
    public function medicalHistory(){
        return $this->belongsTo(MedicalHistory::class);
    }
    public function tariff(){
        return $this->belongsTo(Tariff::class);
    }
    public function ward(){
        return $this->belongsTo(Ward::class);
    }
    public function bed(){
        return $this->belongsTo(Bed::class);
    }
    public function mealType(){
        return $this->belongsTo(MealType::class);
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
    public function calculateBedCost()
    {
        if (!$this->admission_date) {
            return 0;
        }

        $medicalBed = $this->with('tariff')->first();

        if (!$medicalBed || !$medicalBed->tariff) {
            return 0;
        }

            $admissionDate = \Carbon\Carbon::parse($this->admission_date);
            $dischargeDate = \Carbon\Carbon::parse($this->discharge_date);
                

        return $medicalBed->tariff->daily_price * $this->calculateDays();
    }
    public function calculateMealCost()
    {
        try {
            if (!$this->admission_date) {
                return 0;
            }

            $medicalMeal = $this->with('mealType')->first();
            if (!$medicalMeal || !$medicalMeal->mealType) {
                return 0;
            }
            


            $admissionDate = \Carbon\Carbon::parse($this->admission_date);
            $dischargeDate = \Carbon\Carbon::parse($this->discharge_date);
                
            $days = $admissionDate->diffInDays($dischargeDate) + 1;
            // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
            if ($admissionDate->format('H:i') > '12:00' && $days > 0) {
                $days -= 1;
            }
            
            $days = max($days, 1);

            $totalCost = $medicalMeal->mealType->daily_price * $this->calculateDays();
            
            return $totalCost;
            
        } catch (\Exception $e) {
            \Log::error('calculateMealCost error: ' . $e->getMessage());
            return 0;
        }
    }
    public function getBedAndMealCost()
    {
        return $this->calculateBedCost()+$this->calculateMealCost();
    }
    public function getTotalPaid()
    {
        return $this->payments()->sum('amount');
    }
}

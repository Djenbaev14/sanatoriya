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
    // public function payments(){
    //     return $this->hasMany(Payment::class);
    // }
    public function medicalHistory(){
        return $this->belongsTo(MedicalHistory::class);
    }
    public function createdBy(){
        return $this->belongsTo(User::class,'created_id');
    }
    // accommodationAccomplice orqali bir nechta Пациентlar bo'lishi mumkin
    public function partner(){
        return $this->hasOne(Accommodation::class,'main_accommodation_id');
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
    public function returnedAccommodation(){
        return $this->hasOne(ReturnedAccommodation::class);
    }
    public function calculateDays()
    {
            $admission = \Carbon\Carbon::parse($this->admission_date);
            $discharge = \Carbon\Carbon::parse($this->discharge_date);
                
            $start = $admission->hour < 12 ? $admission->copy()->startOfDay() : $admission->copy()->addDay()->startOfDay();
            $end = $discharge->hour >= 12 ? $discharge->copy()->startOfDay()->addDay() : $discharge->copy()->startOfDay();
            $days= max($start->diffInDays($end), 0);
            
            return $days;
    }
    public function calculatePartnerDays()
    {
            $admission = \Carbon\Carbon::parse($this->partner->admission_date);
            $discharge = \Carbon\Carbon::parse($this->partner->discharge_date);
                
            $start = $admission->hour < 12 ? $admission->copy()->startOfDay() : $admission->copy()->addDay()->startOfDay();
            $end = $discharge->hour >= 12 ? $discharge->copy()->startOfDay()->addDay() : $discharge->copy()->startOfDay();
            $days= max($start->diffInDays($end), 0);
            
            return $days;
            
    }
    public function calculateBedCost()
    {
        return $this->tariff_price * $this->calculateDays();
    }
    public function calculateMealCost()
    {
        return $this->meal_price * $this->calculateDays();
    }
    public function calculatePartnerBedCost()
    {
        return $this->partner->tariff_price * $this->calculatePartnerDays();
    }
    public function calculatePartnerMealCost()
    {
        return $this->partner->meal_price * $this->calculatePartnerDays();
    }
    
    public function getTotalCost()
    {
        // agar this partner bo'lmasa, partnerning hisob-kitoblarini hisoblamaymiz
        if (!$this->partner) {
            return $this->calculateBedCost() + $this->calculateMealCost();
        }
        
        return $this->calculateBedCost()+$this->calculateMealCost() +
            $this->calculatePartnerBedCost()+$this->calculatePartnerMealCost();
    }
    // public function getTotalPaid()
    // {
    //     return $this->payments()->where('amount', '>', 0)->sum('amount');
    // }
    // public function getTotalReturned()
    // {
    //     return abs($this->payments()->where('amount', '<', 0)->sum('amount'));
    // }
    // public function getTotalPaidAndReturned()
    // {
    //     return $this->getTotalPaid() - $this->getTotalReturned();
    // }
}

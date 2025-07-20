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
    public function medicalHistory(){
        return $this->belongsTo(MedicalHistory::class);
    }
    public function createdBy(){
        return $this->belongsTo(User::class,'created_id');
    }
    // accommodationAccomplice orqali bir nechta Пациентlar bo'lishi mumkin
    public function partner()
    {
        return $this->hasOne(Accommodation::class, 'main_accommodation_id', 'id');
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
            $partner = $this->partner;

            if (!$partner || !$partner->admission_date || !$partner->discharge_date) {
                return 0; // Agar partner yoki sanalar yo‘q bo‘lsa, 0 qaytariladi
            }

            $admission = \Carbon\Carbon::parse($partner->admission_date);
            $discharge = \Carbon\Carbon::parse($partner->discharge_date);

            $start = $admission->hour < 12
                ? $admission->copy()->startOfDay()
                : $admission->copy()->addDay()->startOfDay();

            $end = $discharge->hour >= 12
                ? $discharge->copy()->startOfDay()->addDay()
                : $discharge->copy()->startOfDay();

            return max($start->diffInDays($end), 0);
    }
    public function calculateBedCost()
    {
        return $this->tariff_price * $this->ward_day;
    }
    public function calculateMealCost()
    {
        return $this->meal_price * $this->meal_day;
    }
    public function calculatePartnerBedCost()
    {
        $partner = $this->partner;
        if (!$partner) return 0;

        return $partner->tariff_price * $partner->ward_day;
    }
    public function calculatePartnerMealCost()
    {
        $partner = $this->partner;
        if (!$partner) return 0;

        return $partner->meal_price * $partner->meal_day;
    }
    
    public function getTotalCost()
    {
        
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

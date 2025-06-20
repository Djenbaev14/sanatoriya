<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AssignedProcedure extends Model
{
    use HasFactory,LogsActivity,SoftDeletes;

    protected $guarded=['id'];
    protected static $logName = 'assigned_procedure';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('assigned_procedure');
    }
    public function patient(){
        return $this->belongsTo(Patient::class);
    }
    public function medicalHistory(){
        return $this->belongsTo(MedicalHistory::class);
    }
    public function doctor(){
        return $this->belongsTo(User::class);
    }
    public function payments(){
        return $this->hasMany(Payment::class);
    }
    public function procedureDetails(){
        return $this->hasMany(ProcedureDetail::class);
    }
    public function statusPayment(){
        return $this->belongsTo(StatusPayment::class);
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
    
    public function getTotalPaidAmount()
    {
        return $this->payments()->sum('amount');
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
            $daysStayed = max(1, $admissionDate->diffInDays($dischargeDate));

            $totalCost = $medicalMeal->mealType->daily_price * $daysStayed;
            
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

        // Yotgan kunlar sonini hisoblash
        $admissionDate = \Carbon\Carbon::parse($this->admission_date);
        $dischargeDate = $this->discharge_date 
            ? \Carbon\Carbon::parse($this->discharge_date)
            : \Carbon\Carbon::now();

        $daysStayed = $admissionDate->diffInDays($dischargeDate) ;

        return $medicalBed->tariff->daily_price * $daysStayed;
    }
    public function getTotalCost()
    {
        return $this->calculateTotalCost()['total_cost'];
    }
}

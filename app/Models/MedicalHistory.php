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
    
    public function medicalMeal(){
        return $this->hasOne(MedicalMeal::class);
    }
    public function medicalBed(){
        return $this->hasOne(MedicalBed::class);
    }


    public function calculateTotalCost()
    {
        $proceduresCost = $this->calculateProceduresCost();
        $bedCost = $this->calculateBedCost();
        $mealCost = $this->calculateMealCost();
        $labTestsCost = $this->calculateLabTestsCost();

        return [
            'procedures_cost' => $proceduresCost,
            'bed_cost' => $bedCost,
            'meal_cost' => $mealCost,
            'lab_tests_cost' => $labTestsCost,
            'total_cost' => $proceduresCost + $bedCost + $mealCost + $labTestsCost
        ];
    }

    /**
     * Protseduralar yig'indisi
     */
    public function calculateProceduresCost()
    {
        return $this->assignedProcedures()
            ->sum(\DB::raw('price * sessions'));
    }
    

    /**
     * Koyga (palata) yig'indisi
     */
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

    /**
     * Ovqat yig'indisi
     */
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
            // Agar discharge_date admission_date dan kichik bo'lsa, 1 kun deb hisoblash
            $daysStayed = max(1, $admissionDate->diffInDays($dischargeDate));

            $totalCost = $medicalMeal->mealType->daily_price * $daysStayed;
            
            return $totalCost;
            
        } catch (\Exception $e) {
            // Debug uchun log yozish
            \Log::error('calculateMealCost error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lab tahlillar yig'indisi
     */
    public function calculateLabTestsCost()
    {
        return $this->labTestHistories()
            ->where('status', '!=', 'cancelled') // Bekor qilingan tahlillarni hisobga olmaydi
            ->sum('price');
    }

    /**
     * Batafsil hisobot
     */
    public function getDetailedReport()
    {
        $medicalHistory = $this->load([
            'assignedProcedures.procedure',
            'medicalBed.tariff',
            'medicalMeal.mealType',
            'labTestHistories.labTest'
        ]);

        // Yotgan kunlar
        $daysStayed = $this->getDaysStayed();

        // Protseduralar detali
        $procedures = $medicalHistory->assignedProcedures->map(function ($assigned) {
            return [
                'name' => $assigned->procedure->name,
                'sessions' => $assigned->sessions,
                'price_per_session' => $assigned->price,
                'total' => $assigned->price * $assigned->sessions
            ];
        });

        // Koyga detali
        $bedInfo = null;
        if ($medicalHistory->medicalBed && $medicalHistory->medicalBed->tariff) {
            $bedInfo = [
                'tariff_name' => $medicalHistory->medicalBed->tariff->name,
                'price_per_day' => $medicalHistory->medicalBed->tariff->price,
                'days_stayed' => $daysStayed,
                'total' => $medicalHistory->medicalBed->tariff->price * $daysStayed
            ];
        }

        // Ovqat detali
        $mealInfo = null;
        if ($medicalHistory->medicalMeal && $medicalHistory->medicalMeal->mealType) {
            $mealInfo = [
                'meal_type' => $medicalHistory->medicalMeal->mealType->name,
                'price_per_day' => $medicalHistory->medicalMeal->mealType->price,
                'days_stayed' => $daysStayed,
                'total' => $medicalHistory->medicalMeal->mealType->price * $daysStayed
            ];
        }

        // Lab tahlillar detali
        $labTests = $medicalHistory->labTestHistories
            ->where('status', '!=', 'cancelled')
            ->map(function ($labHistory) {
                return [
                    'name' => $labHistory->labTest->name,
                    'status' => $labHistory->status,
                    'price' => $labHistory->price
                ];
            });

        return [
            'patient_info' => [
                'admission_date' => $this->admission_date,
                'discharge_date' => $this->discharge_date,
                'days_stayed' => $daysStayed
            ],
            'procedures' => $procedures,
            'bed_info' => $bedInfo,
            'meal_info' => $mealInfo,
            'lab_tests' => $labTests,
        ];
    }

    /**
     * Faqat umumiy yig'indi (tez hisoblash uchun)
     */
    public function getTotalCost()
    {
        return $this->calculateTotalCost()['total_cost'];
    }
    public function getTotalPaidAmount()
    {
        return $this->payments()->sum('amount');
    }

    /**
     * Yotgan kunlar sonini hisoblash (helper metod)
     */
    public function getDaysStayed()
    {
        if (!$this->admission_date) {
            return 0;
        }

        $admissionDate = \Carbon\Carbon::parse($this->admission_date);
        $dischargeDate = $this->discharge_date 
            ? \Carbon\Carbon::parse($this->discharge_date)
            : \Carbon\Carbon::now();
        
        return $admissionDate->diffInDays($dischargeDate) + 1;
    }

    // Accessor'lar (agar kerak bo'lsa)
    public function getTotalCostAttribute()
    {
        return $this->getTotalCost();
    }

    public function getProceduresCostAttribute()
    {
        return $this->calculateProceduresCost();
    }

    public function getBedCostAttribute()
    {
        return $this->calculateBedCost();
    }

    public function getMealCostAttribute()
    {
        return $this->calculateMealCost();
    }

    public function getLabTestsCostAttribute()
    {
        return $this->calculateLabTestsCost();
    }
    
}

<?php

namespace App\Helpers;

use App\Models\MedicalHistory;
use App\Models\AssignedProcedure;
use App\Models\MedicalBed;
use App\Models\MedicalMeal;
use App\Models\LabTestHistory;

    /**
     * Tibbiy tarix uchun umumiy yig'indini hisoblash
     */
    // function calculateTotalCost($medicalHistoryId)
    // {
    //     $proceduresCost = self::calculateProceduresCost($medicalHistoryId);
    //     $bedCost = self::calculateBedCost($medicalHistoryId);
    //     $mealCost = self::calculateMealCost($medicalHistoryId);
    //     $labTestsCost = self::calculateLabTestsCost($medicalHistoryId);

    //     return [
    //         'procedures_cost' => $proceduresCost,
    //         'bed_cost' => $bedCost,
    //         'meal_cost' => $mealCost,
    //         'lab_tests_cost' => $labTestsCost,
    //         'total_cost' => $proceduresCost + $bedCost + $mealCost + $labTestsCost
    //     ];
    // }

    /**
     * Protseduralar yig'indisi
     */
     function calculateProceduresCost($medicalHistoryId)
    {
        return AssignedProcedure::where('medical_history_id', $medicalHistoryId)
            ->sum(\DB::raw('price * sessions'));
    }

    /**
     * Koyga (palata) yig'indisi
     */
     function calculateBedCost($medicalHistoryId)
    {
        $medicalHistory = MedicalHistory::find($medicalHistoryId);
        
        if (!$medicalHistory || !$medicalHistory->admission_date) {
            return 0;
        }

        $medicalBed = MedicalBed::where('medical_history_id', $medicalHistoryId)
            ->with('tariff')
            ->first();

        if (!$medicalBed || !$medicalBed->tariff) {
            return 0;
        }

        // Yotgan kunlar sonini hisoblash
        $admissionDate = \Carbon\Carbon::parse($medicalHistory->admission_date);
        $dischargeDate = $medicalHistory->discharge_date 
            ? \Carbon\Carbon::parse($medicalHistory->discharge_date)
            : \Carbon\Carbon::now();

        $daysStayed = $admissionDate->diffInDays($dischargeDate) + 1; // +1 chunki birinchi kun ham hisobga olinadi

        return $medicalBed->tariff->price * $daysStayed;
    }
    function calculateMealCost($medicalHistoryId)
    {
        $medicalHistory = MedicalHistory::find($medicalHistoryId);
        
        if (!$medicalHistory || !$medicalHistory->admission_date) {
            return 0;
        }

        $medicalMeal = MedicalMeal::where('medical_history_id', $medicalHistoryId)
            ->with('mealType')
            ->first();

        if (!$medicalMeal || !$medicalMeal->mealType) {
            return 0;
        }
        $admissionDate = \Carbon\Carbon::parse($medicalHistory->admission_date);
        $dischargeDate = $medicalHistory->discharge_date 
            ? \Carbon\Carbon::parse($medicalHistory->discharge_date)
            : \Carbon\Carbon::now();

        $daysStayed = $admissionDate->diffInDays($dischargeDate);

        return $medicalMeal->mealType->price * $daysStayed;
    }
     function calculateLabTestsCost($medicalHistoryId)
    {
        return LabTestHistory::where('medical_history_id', $medicalHistoryId)
            ->where('status', '!=', 'cancelled') // Bekor qilingan tahlillarni hisobga olmaydi
            ->sum('price');
    }

    /**
     * Batafsil hisobot
     */
     function getDetailedReport($medicalHistoryId)
    {
        $medicalHistory = MedicalHistory::with([
            'assignedProcedures.procedure',
            'medicalBed.tariff',
            'medicalMeal.mealType',
            'labTestHistories.labTest'
        ])->find($medicalHistoryId);

        if (!$medicalHistory) {
            return null;
        }

        // Yotgan kunlar
        $daysStayed = 0;
        if ($medicalHistory->admission_date) {
            $admissionDate = \Carbon\Carbon::parse($medicalHistory->admission_date);
            $dischargeDate = $medicalHistory->discharge_date 
                ? \Carbon\Carbon::parse($medicalHistory->discharge_date)
                : \Carbon\Carbon::now();
            $daysStayed = $admissionDate->diffInDays($dischargeDate) + 1;
        }

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

        // $totalCost = self::calculateTotalCost($medicalHistoryId);

        return [
            'patient_info' => [
                'admission_date' => $medicalHistory->admission_date,
                'discharge_date' => $medicalHistory->discharge_date,
                'days_stayed' => $daysStayed
            ],
            'procedures' => $procedures,
            'bed_info' => $bedInfo,
            'meal_info' => $mealInfo,
            'lab_tests' => $labTests,
            // 'totals' => $totalCost
        ];
    }

    /**
     * Faqat umumiy yig'indi (tez hisoblash uchun)
     */
    //  function getTotalCost($medicalHistoryId)
    // {
    //     return self::calculateTotalCost($medicalHistoryId)['total_cost'];
    // }
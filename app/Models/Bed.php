<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use HasFactory;
    
    protected $guarded=['id'];
    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }
    public function medicalBeds()
    {
        return $this->hasMany(MedicalBed::class);
    }
    
    
    // Bo'sh koygalarni aniqlash uchun scope
    public function scopeAvailableBeds($query)
    {
        return $query->whereDoesntHave('medicalBeds', function ($subQuery) {
            $subQuery->whereHas('MedicalHistory', function ($historyQuery) {
                $historyQuery->where(function ($dateQuery) {
                    // Aktiv (chiqmagan) Пациентlar
                    $dateQuery->whereNull('discharge_date')
                        ->orWhere('discharge_date', '>', now()->toDateString());
                });
            });
        });
    }   
    // Koyga band yoki yo'qligini tekshirish
    public function isAvailable()
    {
        return !$this->medicalBeds()
            ->whereHas('assignedProcedure', function ($query) {
                $query->where(function ($dateQuery) {
                    $dateQuery->whereNull('discharge_date')
                        ->orWhere('discharge_date', '>', now()->toDateString());
                });
            })
            ->exists();
    } 
    // Hozirgi Пациентni olish
    public function currentPatient()
    {
        return $this->medicalBeds()
            ->whereHas('assignedProcedure', function ($query) {
                $query->where(function ($dateQuery) {
                    $dateQuery->whereNull('discharge_date')
                        ->orWhere('discharge_date', '>', now()->toDateString());
                });
            })
            ->with('assignedProcedure.patient')
            ->first();
    }
}

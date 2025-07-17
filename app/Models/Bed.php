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
    public function accommodations()
    {
        return $this->hasMany(Accommodation::class);
    }
    public function scopeAvailableBeds($query)
    {
        return $query->whereDoesntHave('accommodations', function ($subQuery) {
            $subQuery->where(function ($query) {
                // Chiqmagan yoki hali ketmaganlar
                $query->whereNull('discharge_date')
                    ->orWhere('discharge_date', '>', now());
            });
        });
    } 
    // Koyga band yoki yo'qligini tekshirish
    public function isAvailable()
    {
        return !$this->accommodations()
            ->where(function ($query) {
                $query->whereNull('discharge_date')
                    ->orWhere('discharge_date', '>', now()->toDateString());
            })
            ->exists();
    }
    // Hozirgi Пациентni olish
    public function currentPatient()
    {
        return $this->accommodations()
            ->where(function ($query) {
                $query->whereNull('discharge_date')
                    ->orWhere('discharge_date', '>', now()->toDateString());
            })
            ->with('patient') // bemor ma'lumotlari bilan yuklaydi
            ->latest('admission_date') // eng oxirgi yotgan odamni olamiz
            ->first();
    }   
}

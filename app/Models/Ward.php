<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Ward extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    public function beds()
    {
        return $this->hasMany(Bed::class);
    }
    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }

    // Bo'sh koygalar sonini olish
    public function getAvailableBedsCountAttribute()
    {
        return $this->beds()->availableBeds()->count();
    }
    public function currentPatients(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\Patient::class,
            \App\Models\Accommodation::class,
            'ward_id',    // Accommodation jadvalidagi foreign key
            'id',         // Patient modelining primary key
            'id',         // Ward modelidagi local key
            'patient_id'  // Accommodation jadvalidagi foreign key
        )
        ->where('admission_date', '<=', now()) // qabul qilingan
        ->where(function ($query) {
            $query->whereNull('discharge_date')
                ->orWhere('discharge_date', '>', now()); // chiqmagan
        });
    }

    public function currentAccommodations()
    {
        return $this->hasMany(\App\Models\Accommodation::class)
            ->where('admission_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('discharge_date')
                    ->orWhere('discharge_date', '>', now());
            })
            ->with('patient'); // bemorni ham olish
    }
    public function getCurrentPatientsDisplayAttribute()
    {
        $main = [];
        $accomplices = [];

        foreach ($this->currentAccommodations as $accommodation) {
            $patient = $accommodation->patient;
            if (!$patient) continue;

            $name = $patient->full_name ?? 'Nomaʼlum';

            if ($patient->is_accomplice) {
                $accomplices[] = '🤝 ' . $name;
            } else {
                $main[] = '👤 ' . $name;
            }
        }

        return collect($main)
            ->merge($accomplices)
            ->join(',');
    }
        // Jami koygalar soni
    public function getTotalBedsCountAttribute()
    {
        return $this->beds()->count();
    }
}

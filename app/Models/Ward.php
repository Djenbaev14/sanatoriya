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
    // total_places
    public function getTotalPlacesAttribute()
    {
        return $this->beds()->count();
    }
    // beds_count
    public function getBedsCountAttribute()
    {
        return $this->total_places - $this->available_beds_count;
    }

    // Bo'sh koygalar sonini olish
    public function getAvailableBedsCountAttribute()
    {
        return $this->beds()->availableBeds()->count();
    }

    public function currentAccommodations()
    {
        return $this->hasMany(\App\Models\Accommodation::class)
            ->where('admission_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('discharge_date')
                    ->orWhere('discharge_date', '>', now());
            })
            ->with('patient','medicalHistory.medicalInspection'); // bemorni ham olish
    }
    public function currentAccommodationsForDoctor($doctorId)
    {
        return $this->hasMany(\App\Models\Accommodation::class)
            ->where('is_accomplice', false)
            ->where(function ($query) {
                $query->whereNull('discharge_date')
                    ->orWhere('discharge_date', '>', now());
            })
            ->whereHas('medicalHistory.medicalInspection', function ($query) use ($doctorId) {
                $query->where('assigned_doctor_id', $doctorId);
            })
            ->with('patient');
    }public function getCurrentPatientsDisplayAttribute()
{
    $accommodations = $this->currentAccommodations;

    $filters = request()->input('tableFilters', []);
    $doctorId = data_get($filters, 'doctor.value');

    if ($doctorId) {
        $accommodations = $accommodations->filter(function ($accommodation) use ($doctorId) {
            return $accommodation->medicalHistory &&
                   $accommodation->medicalHistory->medicalInspection &&
                   $accommodation->medicalHistory->medicalInspection->assigned_doctor_id == $doctorId;
        });
    }

    $main = [];
    $accomplices = [];

    foreach ($accommodations as $accommodation) {
        $patient = $accommodation->patient;
        if (!$patient) continue;

        $name = $patient->full_name ?? 'Nomaʼlum';
        $link = route('filament.admin.resources.medical-histories.view', $accommodation->medical_history_id);
        $nameWithLink = "<a href='{$link}' class='text-primary-600 hover:text-primary-800' target='_blank'>{$name}</a>";

        if ($patient->is_accomplice) {
            $accomplices[] = '🤝 ' . $nameWithLink;
        } else {
            $main[] = '👤 ' . $nameWithLink;
        }
    }

    return collect($main)
        ->merge($accomplices)
        ->join(', ');
}

    
}

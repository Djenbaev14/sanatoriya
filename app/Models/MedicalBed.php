<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MedicalBed extends Model
{
    use HasFactory,LogsActivity,SoftDeletes;

    protected $guarded=['id'];
    protected static $logName = 'medical_bed';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('medical_bed');
    }

    public function medicalHistory()
    {
        return $this->belongsTo(MedicalHistory::class);
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }
    
}

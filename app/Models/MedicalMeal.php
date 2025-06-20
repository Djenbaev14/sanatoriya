<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MedicalMeal extends Model
{
    
    use HasFactory,LogsActivity,SoftDeletes;

    protected $guarded=['id'];
    protected static $logName = 'medical_meal';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('medical_meal');
    }
    public function mealType(){
        return $this->belongsTo(MealType::class);
    }
    public function medicalHistory(){
        return $this->belongsTo(MedicalHistory::class);
    }

}

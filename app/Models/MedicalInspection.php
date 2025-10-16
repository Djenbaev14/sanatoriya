<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MedicalInspection extends Model
{
    use HasFactory,LogsActivity,SoftDeletes;

    protected $guarded=['id'];
    protected static $logName = 'medical_inspection';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('medical_inspection');
    }
    public function patient(){
        return $this->belongsTo(Patient::class);
    }
    public function initialDoctor(){
        return $this->belongsTo(User::class,'initial_doctor_id');
    }
    public function mkbClass(){
        return $this->belongsTo(MkbClass::class,'mkb_class_id');
    }  
    public function assignedDoctor(){
        return $this->belongsTo(User::class,'assigned_doctor_id');
    }    
    public function payments(){
        return $this->hasMany(Payment::class);
    }
    public function statusPayment(){
        return $this->belongsTo(StatusPayment::class);
    }
    public function medicalHistory(){
        return $this->belongsTo(MedicalHistory::class,'medical_history_id');
    }
    
    public function getTotalPaidAmount()
    {
        return $this->payments()->sum('amount');
    }
}

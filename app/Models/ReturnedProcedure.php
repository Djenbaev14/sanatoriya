<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ReturnedProcedure extends Model
{
    
    use HasFactory,LogsActivity,SoftDeletes;

    protected $guarded=['id'];
    protected static $logName = 'returned_procedure';
    protected static $logOnlyDirty = true;
    
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function medicalHistory()
    {
        return $this->belongsTo(MedicalHistory::class);
    }
    public function assignedProcedure()
    {
        return $this->belongsTo(AssignedProcedure::class);
    }
    public function returnedProcedureDetails()
    {
        return $this->hasMany(ReturnedProcedureDetail::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('returned_procedure');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProcedureDetail extends Model
{
    
    use HasFactory,LogsActivity,SoftDeletes;

    protected $guarded=['id'];
    protected static $logName = 'procedure_detail';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('procedure_detail');
    }
    public function procedure(){
        return $this->belongsTo(Procedure::class);
    }
    public function performer(){
        return $this->belongsTo(User::class);
    }
    public function assignedProcedure(){
        return $this->belongsTo(AssignedProcedure::class);
    }
    public function procedureSessions(){
        return $this->hasMany(ProcedureSession::class);
    }
}

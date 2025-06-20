<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LabTestDetail extends Model
{
    
    use HasFactory,LogsActivity,SoftDeletes;

    protected $guarded=['id'];
    protected static $logName = 'lab_test_detail';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('lab_test_detail');
    }

    public function lab_test_history(){
        return $this->belongsTo(LabTestHistory::class);
    }
    public function lab_test(){
        return $this->belongsTo(LabTest::class);
    }
}

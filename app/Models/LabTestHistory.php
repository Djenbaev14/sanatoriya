<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LabTestHistory extends Model
{
    
    use HasFactory,LogsActivity,SoftDeletes;

    protected $guarded=['id'];
    protected static $logName = 'lab_test_history';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('lab_test_history');
    }
    public function patient(){
        return $this->belongsTo(Patient::class);
    }
    public function labTestDetails(){
        return $this->hasMany(LabTestDetail::class);
    }
    // public function payments(){
    //     return $this->hasMany(Payment::class);
    // }
    public function doctor(){
        return $this->belongsTo(User::class);
    }
    public function statusPayment(){
        return $this->belongsTo(StatusPayment::class);
    }
    public function medical_history(){
        return $this->belongsTo(MedicalHistory::class);
    }
    
    public function getTotalCost()
    {
        return $this->labTestDetails()
            ->sum(\DB::raw('price * sessions'));
    }
    // public function getTotalPaidAmount()
    // {
    //     return $this->payments()->where('amount', '>', 0)->sum('amount');
    // }
    // public function getTotalReturned()
    // {
    //     return abs($this->payments()->where('amount', '<', 0)->sum('amount'));
    // }
    // public function getTotalPaidAndReturned()
    // {
    //     return $this->getTotalPaidAmount() - $this->getTotalReturned();
    // }
    public function getTotalCostAttribute()
    {
        return $this->getTotalCost(); // oldingi metoddan foydalandik
    }
    

    // public function getTotalPaidAmountAttribute()
    // {
    //     return $this->getTotalPaidAmount();
    // }
    // public function getTotalDebtAmountAttribute()
    // {
    //     return $this->getTotalCost() - $this->getTotalPaidAmount();
    // }

    protected static function booted()
    {
        static::creating(function ($model) {
            if ($model->lab_test_id && is_null($model->price)) {
                $model->price = \App\Models\LabTest::find($model->lab_test_id)?->price ?? 0;
            }
        });
    }
}

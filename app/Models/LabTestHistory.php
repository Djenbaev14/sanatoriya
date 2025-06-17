<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestHistory extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    public function patient(){
        return $this->belongsTo(Patient::class);
    }
    public function labTestDetails(){
        return $this->hasMany(LabTestDetail::class);
    }
    public function payments(){
        return $this->hasMany(Payment::class);
    }
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
    public function getTotalPaidAmount()
    {
        return $this->payments()->sum('amount');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if ($model->lab_test_id && is_null($model->price)) {
                $model->price = \App\Models\LabTest::find($model->lab_test_id)?->price ?? 0;
            }
        });
    }
}

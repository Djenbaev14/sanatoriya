<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    
    use HasFactory,LogsActivity;

    protected $guarded=['id'];
    protected static $logName = 'payment';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('payment');
    }
    public function patient() {
        return $this->belongsTo(Patient::class);
    }

    public function paymentType() {
        return $this->belongsTo(PaymentType::class);
    }

    public function getPaymentReasonAttribute(): string
    {
        if ($this->accommodation_id) {
            return 'Палата (койка)';
        }

        if ($this->assigned_procedure_id) {
            return 'Лечение (лечение процедуры)';
        }

        if ($this->lab_test_history_id) {
            return 'Анализ (лаборатория)';
        }

        return $this->description ?? 'Неизвестно';
    }
}

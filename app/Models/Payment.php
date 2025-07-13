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
    
    public function medicalHistory(){
        return $this->belongsTo(MedicalHistory::class);
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
    protected static function booted()
    {
        static::created(function ($payment) {
            // Faqat cashboxdan o‘tadigan payment_type lar (masalan: 1 - Наличные, 2 - Терминал)
            if (in_array($payment->payment_type_id, [1, 2])) {
                $session = \App\Models\CashboxSession::query()
                    ->whereDate('date', today())
                    ->where('payment_type_id', $payment->payment_type_id)
                    ->whereNull('closed_by')
                    ->latest()
                    ->first();
\Log::info('Session topildi: ', ['session' => $session]);
                if ($session) {
                    $session->increment('closing_amount', $payment->amount);
                }
            }else{
                BankTransfer::create([
                    'amount'=>$payment->amount,
                    'commission_percent'=>PaymentType::find($payment->payment_type_id)->commission_percent,
                    'payment_type_id'=>$payment->payment_type_id,
                    'transferred_at'=>$payment->created_at
                ]);
            }
        });
        static::deleting(function ($payment) {
            if (in_array($payment->payment_type_id, [1, 2])) {
                $session = \App\Models\CashboxSession::query()
                    ->whereDate('date', today())
                    ->where('payment_type_id', $payment->payment_type_id)
                    ->whereNull('closed_by')
                    ->latest()
                    ->first();

                if ($session) {
                    $session->decrement('closing_amount', $payment->amount);
                }
            }
        });

    }

}

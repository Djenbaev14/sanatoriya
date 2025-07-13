<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BankTransfer extends Model
{
    use HasFactory,LogsActivity;
    
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('BankTransfer');
    }
    protected $guarded = ['id'];
    
    
    public function paymentType() {
        return $this->belongsTo(PaymentType::class);
    }
    public function getPaymentTypeNameAttribute(): string
    {
        return $this->paymentType ? $this->paymentType->name : 'Неизвестно';
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

                if ($session) {
                    $session->decrement('closing_amount', $payment->amount);
                }
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
                    $session->increment('closing_amount', $payment->amount);
                }
            }
        });

    }
    
}

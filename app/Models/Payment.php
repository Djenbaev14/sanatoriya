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
            if (in_array($payment->payment_type_id, [2, 3])) {
                BankTransfer::create([
                    'amount'=>$payment->total_paid,
                    'commission_percent'=>PaymentType::find($payment->payment_type_id)->commission_percent,
                    'payment_type_id'=>$payment->payment_type_id,
                    'transferred_at'=>$payment->created_at
                ]);
            }else{
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
        static::deleting(function ($payment) {
            if ($payment->payment_type_id==1) {
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
    public function labTestPayments()
    {
        return $this->hasMany(\App\Models\LabTestPayment::class);
    }

    public function procedurePayments()
    {
        return $this->hasMany(\App\Models\ProcedurePayment::class);
    }

    public function accommodationPayments()
    {
        return $this->hasMany(\App\Models\AccommodationPayment::class);
    }
    public function getTotalPaidAmount(): float
    {
        // Lab test summasi
        $labTestTotal = $this->labTestPayments()
            ->with('labTestPaymentDetails')
            ->get()
            ->flatMap->labTestPaymentDetails
            ->sum(function ($detail) {
                return ($detail->price ?? 0) * ($detail->sessions ?? 1);
            });

        // Procedure summasi
        $procedureTotal = $this->procedurePayments()
            ->with('procedurePaymentDetails')
            ->get()
            ->flatMap->procedurePaymentDetails
            ->sum(function ($detail) {
                return ($detail->price ?? 0) * ($detail->sessions ?? 1);
            });

        // Accommodation (koyka + ovqat)
       $accommodationTotal = $this->accommodationPayments()
            ->get()
            ->sum(function ($acc) {
                $ward = ($acc->tariff_price ?? 0) * ($acc->ward_day ?? 1);
                $meal = ($acc->meal_price ?? 0) * ($acc->meal_day ?? 1);
                return $ward + $meal;
            });


        return $labTestTotal + $procedureTotal + $accommodationTotal;
    }


}

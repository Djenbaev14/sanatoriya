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
    
}

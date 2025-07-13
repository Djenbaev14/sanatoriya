<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CashboxSession extends Model
{
    use HasFactory,LogsActivity;
    
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('CashboxSession');
    }
    protected $guarded = ['id'];
    // openedBy,closedBy relations are assumed to be defined in the User model
    protected $casts = [
        'opening_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2',
    ];
    protected $table = 'cashbox_sessions';
    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }
    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
    public function paymentType(){
        return $this->belongsTo(PaymentType::class);
    }
}

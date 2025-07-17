<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AssignedProcedure extends Model
{
    use HasFactory,LogsActivity,SoftDeletes;

    protected $guarded=['id'];
    protected static $logName = 'assigned_procedure';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('assigned_procedure');
    }
    public function patient(){
        return $this->belongsTo(Patient::class);
    }
    public function medicalHistory(){
        return $this->belongsTo(MedicalHistory::class);
    }
    public function doctor(){
        return $this->belongsTo(User::class);
    }
    // public function payments(){
    //     return $this->hasMany(Payment::class);
    // }
    public function procedureDetails(){
        return $this->hasMany(ProcedureDetail::class);
    }
    public function statusPayment(){
        return $this->belongsTo(StatusPayment::class);
    }
    
    

    
    
    public function getTotalCost()
    {
        return $this->procedureDetails()
             ->sum(\DB::raw('price * sessions'));
    }
    

    public function getTotalCostAttribute()
    {
        return $this->getTotalCost(); // oldingi metoddan foydalandik
    }

    public function payments()
    {
        return $this->hasMany(ProcedurePayment::class);
    }
    
    public function paymentDetails()
    {
        return $this->hasMany(ProcedurePaymentDetail::class);
    }

    // public function getTotalPriceAttribute()
    // {
    //     return $this->procedureDetails->sum(fn($d) => $d->price * $d->sessions);
    // }

    public function getTotalPaidAttribute()
    {
        return $this->paymentDetails->sum(fn($d) => $d->price * $d->sessions);
    }

    public function getDebtAttribute()
    {
        return $this->total_cost - $this->total_paid;
    }
}

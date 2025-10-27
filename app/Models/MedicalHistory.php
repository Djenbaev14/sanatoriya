<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MedicalHistory extends Model
{
    use HasFactory,LogsActivity,SoftDeletes;
    protected $guarded=['id'];

    protected $casts = [
        'disability_types' => 'array',
    ];
    

    protected static $logName = 'medical_history';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('medical_history');
    }
    public function departmentInspection(){
        return $this->hasOne(DepartmentInspection::class);
    }
    public function BedMealstatusPayment(){
        return $this->belongsTo(StatusPayment::class,'status_payment_id');
    }
    public function createdBy(){
        return $this->belongsTo(User::class,'created_id');
    }
    public function patient(){
        return $this->belongsTo(Patient::class);
    }
    public function doctor(){
        return $this->belongsTo(User::class);
    }
    public function assignedProcedure(){
        return $this->hasOne(AssignedProcedure::class);
    }
    public function returnedProcedures(){
        return $this->hasMany(ReturnedProcedure::class);
    }
    public function labTestHistory(){
        return $this->hasOne(LabTestHistory::class);
    }
    public function medicalInspection()
    {
        return $this->hasOne(\App\Models\MedicalInspection::class, 'medical_history_id');
    }
    public function accommodation()
    {
        return $this->hasOne(\App\Models\Accommodation::class, 'medical_history_id');
    }
    public function partnerAccommodation()
    {
        return $this->hasOne(Accommodation::class, 'main_accommodation_id', 'id')
            ->whereNotNull('main_accommodation_id');
    }
    public function getMedicalInspectionAttribute(){
        return $this->medicalInspection()->first();
    }
    
    public function payments(){
        return $this->hasMany(Payment::class,'medical_history_id');
    }
    
    public function getTotalCost(){
        $procedureCost = $this->assignedProcedure?->getTotalCost() ?? 0;
        $accommodationCost = $this->accommodation?->getTotalCost() ?? 0;
        $labTestCost = $this->labTestHistory?->getTotalCost() ?? 0;

        return $procedureCost + $accommodationCost + $labTestCost;
    }
    public function getTotalCostAttribute()
    {
        return $this->getTotalCost();
    }
    
    public function getUnpaidProcedureSessions($procedureDetail)
    {
        $paidSessions = ProcedurePaymentDetail::where('assigned_procedure_id', $procedureDetail->assigned_procedure_id)
            ->where('procedure_id', $procedureDetail->procedure_id)
            ->sum('sessions');

        return max(0, $procedureDetail->sessions - $paidSessions);
    }
    public function getUnpaidLabSessions($labDetail)
    {
        $paid = LabTestPaymentDetail::where('lab_test_history_id', $labDetail->lab_test_history_id)
            ->where('lab_test_id', $labDetail->lab_test_id)
            ->sum('sessions');

        return max(0, $labDetail->sessions - $paid);
    }
    
    public function accommodationPayments()
    {
        return $this->hasMany(\App\Models\AccommodationPayment::class);
    }

    public function getUnpaidWardDays()
    {
        $paid = $this->accommodationPayments->sum('ward_day');
        return max(0, $this->accommodation?->ward_day - $paid);
    }

    public function getUnpaidMealDays()
    {
        $paid = $this->accommodationPayments->sum('meal_day');
        return max(0, $this->accommodation?->meal_day - $paid);
    }
    public function getUnpaidPartnerWardDays()
    {
        $partnerAccommodation = $this->accommodation?->partner;
        if (!$partnerAccommodation) {
            return 0; // yoki null, yoki boshqa default qiymat
        }
        $paid = AccommodationPayment::where('accommodation_id','=',$partnerAccommodation?->id)->sum('ward_day');
        return max(0, $partnerAccommodation->ward_day - $paid);
    }
    public function getUnpaidPartnerMealDays()
    {
        $partnerAccommodation = $this->accommodation?->partner;

        if (!$partnerAccommodation) {
            return 0; // yoki null, yoki boshqa default qiymat
        }
        $paid = AccommodationPayment::where('accommodation_id','=',$partnerAccommodation?->id)->sum('meal_day');
        return max(0, $partnerAccommodation->meal_day - $paid);
    }
    public function getRemainingDebtAttribute(): float
    {
        return max(0, $this->getTotalCostAttribute() - $this->getTotalPaidSumAttribute());
    }
    public function scopeWithDebt($query)
    {
        return $query->whereRaw('
            (
                COALESCE((
                    SELECT SUM(price * sessions) FROM assigned_procedures 
                    JOIN procedure_details ON assigned_procedures.id = procedure_details.assigned_procedure_id 
                    WHERE assigned_procedures.medical_history_id = medical_histories.id
                ), 0)
                +
                COALESCE((
                    SELECT SUM(price * sessions) FROM lab_test_histories 
                    JOIN lab_test_details ON lab_test_histories.id = lab_test_details.lab_test_history_id 
                    WHERE lab_test_histories.medical_history_id = medical_histories.id
                ), 0)
                +
                COALESCE((
                    SELECT COALESCE(tariff_price, 0) + COALESCE(meal_price, 0) FROM accommodations 
                    WHERE accommodations.medical_history_id = medical_histories.id LIMIT 1
                ), 0)
            ) > COALESCE((
                SELECT SUM(amount) FROM payments 
                WHERE payments.medical_history_id = medical_histories.id
            ), 0)
        ');
    }

    public function getTotalWardPaymentAttribute(): int
    {
        return $this->accommodationPayments()
            ->selectRaw('SUM(ward_day * tariff_price) as total')
            ->value('total') ?? 0;
    }
    public function getTotalPaidSumAttribute()
    {
        return
            ($this->total_ward_payment ?? 0) +
            ($this->total_meal_payment ?? 0) +
            ($this->total_medical_services_payment ?? 0) +
            ($this->total_ward_payment_partner ?? 0) +
            ($this->total_meal_payment_partner ?? 0);
    }
    public function getTotalMealPaymentAttribute(): int
    {
        return $this->accommodationPayments()
            ->selectRaw('SUM(meal_day * meal_price) as total')
            ->value('total') ?? 0;
    }
    public function getTotalWardPaymentPartnerAttribute()
    {
        if (!$this->accommodation || !$this->accommodation?->partner) {
            return 0;
        }

        return AccommodationPayment::where('accommodation_id', $this->accommodation?->partner->id)
            ->selectRaw('SUM(ward_day * tariff_price) as total')
            ->value('total') ?? 0;
    }
    public function getTotalMealPaymentPartnerAttribute()
    {
        if (!$this->accommodation || !$this->accommodation?->partner) {
            return 0;
        }

        return AccommodationPayment::where('accommodation_id', $this->accommodation?->partner->id)
            ->selectRaw('SUM(meal_day * meal_price) as total')
            ->value('total') ?? 0;
    }
    
    public function getTotalPaidAmount(): float
    {
        return $this->payments->sum(fn ($payment) => $payment->getTotalPaidAmount());
    }
    public function getTotalProcedurePaymentAttribute(): float
    {
        return \App\Models\ProcedurePaymentDetail::whereHas('procedurePayment.payment', function ($query) {
            $query->where('medical_history_id', $this->id);
        })->sum(\DB::raw('price * sessions')) ?? 0;
    }
    public function getTotalLabTestPaymentAttribute(): float
    {
        return \App\Models\LabTestPaymentDetail::whereHas('labTestPayment.payment', function ($query) {
            $query->where('medical_history_id', $this->id);
        })->sum(\DB::raw('price * sessions')) ?? 0;
    }
    public function getTotalMedicalServicesPaymentAttribute(): float
    {
        return $this->total_lab_test_payment + $this->total_procedure_payment;
    }

}

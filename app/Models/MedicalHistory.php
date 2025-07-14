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
    
    public function getTotalPaidAmount()
    {
        return $this->payments()->where('amount', '>', 0)->sum('amount');
    }
    
    public function getTotalReturned()
    {
        return abs($this->payments()->where('amount', '<', 0)->sum('amount'));
    }
    
    public function getTotalPaidAndReturned()
    {
        return $this->getTotalPaidAmount() - $this->getTotalReturned();
    }
    public function getRemainingDebt(): float
    {
        return max(0, $this->getTotalCost() - $this->getTotalPaidAmount());
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

}

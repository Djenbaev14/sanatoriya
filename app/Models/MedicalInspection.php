<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalInspection extends Model
{
    use HasFactory;

    protected $guarded=['id'];
    public function inspectionDetails()
    {
        return $this->hasMany(\App\Models\InspectionDetail::class, 'medical_inspection_id');
    }
    public function patient(){
        return $this->belongsTo(Patient::class);
    }    public function payments(){
        return $this->hasMany(Payment::class);
    }
    public function statusPayment(){
        return $this->belongsTo(StatusPayment::class);
    }
    public function medical_history(){
        return $this->belongsTo(MedicalHistory::class);
    }
    
    public function getTotalCost()
    {
        return $this->inspectionDetails()
            ->sum('price');
    }
    public function getTotalPaidAmount()
    {
        return $this->payments()->sum('amount');
    }

}

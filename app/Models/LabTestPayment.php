<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestPayment extends Model
{
    use HasFactory;

    protected $guarded=['id'];
    public function labTestPaymentDetails()
    {
        return $this->hasMany(\App\Models\LabTestPaymentDetail::class);
    }
    public function payment()
    {
        return $this->belongsTo(\App\Models\Payment::class);
    }

}

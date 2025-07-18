<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestPaymentDetail extends Model
{
    use HasFactory;
    protected $guarded=['id'];

    public function labTestPayment(){
        return $this->belongsTo(LabTestPayment::class);
    }
    public function labTest(){
        return $this->belongsTo(LabTest::class);
    }
}

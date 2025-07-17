<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedurePayment extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    public function procedurePaymentDetails()
{
    return $this->hasMany(\App\Models\ProcedurePaymentDetail::class);
}

}

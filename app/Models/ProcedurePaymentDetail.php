<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedurePaymentDetail extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    
    public function procedurePayment(){
        return $this->belongsTo(ProcedurePayment::class);
    }
    public function procedure(){
        return $this->belongsTo(Procedure::class);
    }
}

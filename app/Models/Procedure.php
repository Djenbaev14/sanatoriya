<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    use HasFactory; 
    
    protected $guarded=['id'];
    protected $casts = [
        'is_operation' => 'boolean',
    ];
    public function procedureMkbs(){
        return $this->hasMany(ProcedureMkb::class);
    }
    public function procedurePaymentDetails(){
        return $this->hasMany(ProcedurePaymentDetail::class);
    }
    public function mkbClasses()
    {
        return $this->belongsToMany(MkbClass::class, 'procedure_mkbs');
    }

    protected static function booted()
{
    static::creating(function ($procedure) {
        if (is_null($procedure->price_foreign)) {
            $procedure->price_foreign = $procedure->price_per_day;
        }
    });
}
}

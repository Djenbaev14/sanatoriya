<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class Procedure extends Model
{
    use HasFactory; 
    
    protected $guarded=['id'];
    protected $casts = [
        'is_operation' => 'boolean',
    ];
    public function mkbClasses()
    {
        return $this->belongsToMany(
            \App\Models\MkbClass::class,
            'procedure_mkbs',
            'procedure_id',
            'mkb_class_id'
        );
    }
    public function sessions()
    {
        return $this->hasMany(\App\Models\ProcedureSession::class, 'procedure_id');
    }
    // role bilan aloqasi
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'procedure_roles',
            'procedure_id',
            'user_id'
        );
    }
    public function timeCategory()
    {    
        return $this->belongsTo(TimeCategory::class);
    }
    public function procedureMkbs(){
        return $this->hasMany(ProcedureMkb::class);
    }
    public function details(){
        return $this->hasMany(ProcedureDetail::class);
    }

    public function procedurePaymentDetails(){
        return $this->hasMany(ProcedurePaymentDetail::class);
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

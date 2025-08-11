<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTest extends Model
{
    use HasFactory;

    protected $guarded=['id'];
    
    public function mkbClasses()
    {
        return $this->belongsToMany(
            \App\Models\MkbClass::class,
            'lab_test_mkbs',
            'lab_test_id',
            'mkb_class_id'
        );
    }
    
    public function LabTestMkbs(){
        return $this->hasMany(LabTestMkb::class);
    }
    protected static function booted()
{
    static::creating(function ($procedure) {
        if (is_null($procedure->price_foreign)) {
            $procedure->price_foreign = $procedure->price;
        }
    });
}
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedureDetail extends Model
{
    use HasFactory;
    
    protected $guarded=['id'];
    public function procedure(){
        return $this->belongsTo(User::class);
    }
    public function assignedProcedure(){
        return $this->belongsTo(AssignedProcedure::class);
    }
}

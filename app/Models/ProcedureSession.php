<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedureSession extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public function procedureDetail(){
        return $this->belongsTo(ProcedureDetail::class);
    }
    public function procedure(){
        return $this->belongsTo(Procedure::class);
    }
    public function time(){
        return $this->belongsTo(Time::class);
    }
    public function executor(){
        return $this->belongsTo(User::class);
    }
    public function assignedProcedure(){
        return $this->belongsTo(AssignedProcedure::class);
    }
}

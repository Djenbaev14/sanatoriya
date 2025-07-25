<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MkbClass extends Model
{
    use HasFactory;
    protected $guarded=[];
    public function procedureMkbs(){
        return $this->hasMany(ProcedureMkb::class);
    }
    public function procedures()
    {
        return $this->belongsToMany(Procedure::class, 'procedure_mkbs');
    }
}

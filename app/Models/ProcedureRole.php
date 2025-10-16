<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedureRole extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    
    // procedure model bilan aloqasi
    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }
    // role model bilan aloqasi
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

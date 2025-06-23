<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnedProcedureDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    public function procedure()
{
    return $this->belongsTo(Procedure::class);
}

    public function returnedProcedure()
{
    return $this->belongsTo(ReturnedProcedure::class);
}
}

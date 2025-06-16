<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusPayment extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    public function labTestHistories(){
        return $this->hasMany(LabTestHistory::class);
    }
}

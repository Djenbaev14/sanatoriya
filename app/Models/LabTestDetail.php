<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestDetail extends Model
{
    use HasFactory;

    protected $guarded=['id'];

    public function lab_test_history(){
        return $this->belongsTo(LabTestHistory::class);
    }
    public function lab_test(){
        return $this->belongsTo(LabTest::class);
    }
}

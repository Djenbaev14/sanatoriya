<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestHistory extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    protected static function booted()
    {
        static::creating(function ($model) {
            if ($model->lab_test_id && is_null($model->price)) {
                $model->price = \App\Models\LabTest::find($model->lab_test_id)?->price ?? 0;
            }
        });
    }
}

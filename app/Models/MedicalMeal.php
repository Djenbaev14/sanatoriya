<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalMeal extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    public function mealType(){
        return $this->belongsTo(MealType::class);
    }
    public function medicalHistory(){
        return $this->belongsTo(MedicalHistory::class);
    }

}

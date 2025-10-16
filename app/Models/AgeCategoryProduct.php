<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgeCategoryProduct extends Model
{
    use HasFactory;
    protected $fillable = ['age_category_id', 'product_id', 'quantity'];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function ageCategory()
    {
        return $this->belongsTo(AgeCategory::class);
    }
    
}

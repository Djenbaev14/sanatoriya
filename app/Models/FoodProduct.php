<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodProduct extends Model
{
    use HasFactory;
    protected $guarded = [' id'];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function food()
    {
        return $this->belongsTo(Food::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [' id'];
    // union relation
    public function union()
    {
        return $this->belongsTo(Union::class);
    }
    // comingProduct relation
    public function comingProducts()
    {
        return $this->hasMany(ComingProduct::class);
    }
    public function getTotalQuantityAttribute()
    {
        $comingSum = $this->comingProducts()->sum('quantity');
        return $this->warehouse_quan + $comingSum;
    }
    public function ageCategoryProducts()
    {
        return $this->hasMany(AgeCategoryProduct::class);
    }
}

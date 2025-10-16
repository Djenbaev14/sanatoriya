<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierDelivery extends Model
{
    use HasFactory;
    protected $fillable = ['supplier_id', 'delivery_date'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function wards()
    {
        return $this->hasMany(Ward::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeCategory extends Model
{
    use HasFactory;
    protected $fillable = ['name'];
    // relationships times
    public function times()
    {
        return $this->hasMany(Time::class);
    }
}

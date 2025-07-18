<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mkb extends Model
{
    use HasFactory;

    protected $guarded=['id'];
    protected $fillable = ['mkb_code', 'mkb_name'];
}

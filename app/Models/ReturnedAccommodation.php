<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnedAccommodation extends Model
{
    use HasFactory;

    protected $guarded=['id'];
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function medicalHistory()
    {
        return $this->belongsTo(MedicalHistory::class);
    }
    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }
}

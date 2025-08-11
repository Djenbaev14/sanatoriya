<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MkbClass extends Model
{
    use HasFactory;
    protected $guarded=[];
    public function procedureMkbs()
    {
        return $this->hasMany(\App\Models\ProcedureMkb::class, 'mkb_class_id');
    }
    public function labTestMkbs()
    {
        return $this->hasMany(\App\Models\LabTestMkb::class, 'mkb_class_id');
    }
    public function procedures()
    {
        return $this->belongsToMany(
            \App\Models\Procedure::class,
            'procedure_mkbs',        // pivot table nomi
            'mkb_class_id',          // ushbu modelga tegishli foreign key
            'procedure_id'           // boshqa modelga tegishli foreign key
        );
    }
    
    public function labTests()
    {
        return $this->belongsToMany(
            \App\Models\LabTest::class,
            'lab_test_mkbs',        // pivot table nomi
            'mkb_class_id',          // ushbu modelga tegishli foreign key
            'lab_test_id'           // boshqa modelga tegishli foreign key
        );
    }
    // App\Models\MkbClass.php
    public function getAllParentIds(): array
    {
        $ids = [];
        $current = $this;

        while ($current && $current->parent_id) {
            $ids[] = $current->parent_id;
            $current = MkbClass::find($current->parent_id);
        }

        return $ids;
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MkbClass extends Model
{
    use HasFactory;
    protected $guarded=[];
    public function procedureMkbs(){
        return $this->hasMany(ProcedureMkb::class);
    }
    public function procedures()
    {
        return $this->belongsToMany(Procedure::class, 'procedure_mkbs');
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

<?php

namespace App\Imports;

use App\Models\MkbClass;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

class MkbClassImport implements ToModel
{
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {
        return new MkbClass([
            'id' => $row[0],   // Excelda ustun nomlari: mkb_code, mkb_name
            'parent_id' => $row[1],
            'name' => $row[2],
            'has_child' => $row[3],
            'node_cd' => $row[4],
        ]);
    }
}

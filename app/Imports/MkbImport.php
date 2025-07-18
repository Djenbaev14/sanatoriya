<?php

namespace App\Imports;

use App\Models\Mkb;
use Maatwebsite\Excel\Concerns\ToModel;

class MkbImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Mkb([
            'mkb_code' => $row[0],   // Excelda ustun nomlari: mkb_code, mkb_name
            'mkb_name' => $row[1],
        ]);
    }
}

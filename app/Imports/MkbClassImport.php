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
            'name' => $row[1],
        ]);
    }
}

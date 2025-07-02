<?php

namespace App\Filament\Resources\MyPatientResource\Pages;

use App\Filament\Resources\MyPatientResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMyPatients extends ListRecords
{
    protected static string $resource = MyPatientResource::class;

}

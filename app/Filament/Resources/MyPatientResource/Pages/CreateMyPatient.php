<?php

namespace App\Filament\Resources\MyPatientResource\Pages;

use App\Filament\Resources\MyPatientResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMyPatient extends CreateRecord
{
    protected static string $resource = MyPatientResource::class;
}

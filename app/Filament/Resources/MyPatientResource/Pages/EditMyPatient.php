<?php

namespace App\Filament\Resources\MyPatientResource\Pages;

use App\Filament\Resources\MyPatientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMyPatient extends EditRecord
{
    protected static string $resource = MyPatientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

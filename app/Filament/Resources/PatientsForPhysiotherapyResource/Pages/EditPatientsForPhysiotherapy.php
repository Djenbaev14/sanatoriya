<?php

namespace App\Filament\Resources\PatientsForPhysiotherapyResource\Pages;

use App\Filament\Resources\PatientsForPhysiotherapyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPatientsForPhysiotherapy extends EditRecord
{
    protected static string $resource = PatientsForPhysiotherapyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

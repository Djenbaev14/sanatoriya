<?php

namespace App\Filament\Resources\MedicalInspectionResource\Pages;

use App\Filament\Resources\MedicalInspectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedicalInspections extends ListRecords
{
    protected static string $resource = MedicalInspectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

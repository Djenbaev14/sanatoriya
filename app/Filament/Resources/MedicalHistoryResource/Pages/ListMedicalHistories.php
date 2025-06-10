<?php

namespace App\Filament\Resources\MedicalHistoryResource\Pages;

use App\Filament\Resources\MedicalHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedicalHistories extends ListRecords
{
    protected static string $resource = MedicalHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

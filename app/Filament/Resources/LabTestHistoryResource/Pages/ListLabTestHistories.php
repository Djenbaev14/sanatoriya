<?php

namespace App\Filament\Resources\LabTestHistoryResource\Pages;

use App\Filament\Resources\LabTestHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLabTestHistories extends ListRecords
{
    protected static string $resource = LabTestHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

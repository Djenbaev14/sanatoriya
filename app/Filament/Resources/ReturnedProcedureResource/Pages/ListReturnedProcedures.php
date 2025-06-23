<?php

namespace App\Filament\Resources\ReturnedProcedureResource\Pages;

use App\Filament\Resources\ReturnedProcedureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReturnedProcedures extends ListRecords
{
    protected static string $resource = ReturnedProcedureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

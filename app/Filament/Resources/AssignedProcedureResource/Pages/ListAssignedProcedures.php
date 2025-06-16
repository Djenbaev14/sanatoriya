<?php

namespace App\Filament\Resources\AssignedProcedureResource\Pages;

use App\Filament\Resources\AssignedProcedureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssignedProcedures extends ListRecords
{
    protected static string $resource = AssignedProcedureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

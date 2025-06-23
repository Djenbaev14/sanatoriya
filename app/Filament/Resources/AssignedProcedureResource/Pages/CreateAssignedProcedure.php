<?php

namespace App\Filament\Resources\AssignedProcedureResource\Pages;

use App\Filament\Resources\AssignedProcedureResource;
use App\Filament\Resources\PatientResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAssignedProcedure extends CreateRecord
{
    protected static string $resource = AssignedProcedureResource::class;
    protected function getRedirectUrl(): string
    {
        return AssignedProcedureResource::getUrl('view', [
            'record' => $this->record->id,
        ]);
    }
}

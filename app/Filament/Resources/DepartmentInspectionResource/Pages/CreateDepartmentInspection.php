<?php

namespace App\Filament\Resources\DepartmentInspectionResource\Pages;

use App\Filament\Resources\DepartmentInspectionResource;
use App\Filament\Resources\PatientResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartmentInspection extends CreateRecord
{
    protected static string $resource = DepartmentInspectionResource::class;
    protected function getRedirectUrl(): string
    {
        return PatientResource::getUrl('view', [
            'record' => $this->record->patient_id,
        ]);
    }
}

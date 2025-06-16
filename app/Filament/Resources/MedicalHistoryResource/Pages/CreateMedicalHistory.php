<?php

namespace App\Filament\Resources\MedicalHistoryResource\Pages;

use App\Filament\Resources\MedicalHistoryResource;
use App\Filament\Resources\PatientResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMedicalHistory extends CreateRecord
{
    protected static string $resource = MedicalHistoryResource::class;
    protected function getRedirectUrl(): string
    {
        return PatientResource::getUrl('view', [
            'record' => $this->record->patient_id,
        ]);
    }
}

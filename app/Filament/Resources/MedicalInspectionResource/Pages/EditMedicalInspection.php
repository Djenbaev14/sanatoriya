<?php

namespace App\Filament\Resources\MedicalInspectionResource\Pages;

use App\Filament\Resources\MedicalHistoryResource;
use App\Filament\Resources\MedicalInspectionResource;
use App\Filament\Resources\PatientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedicalInspection extends EditRecord
{
    protected static string $resource = MedicalInspectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    

    protected function getRedirectUrl(): string
    {
        return MedicalHistoryResource::getUrl('view', [
            'record' => $this->record->medical_history_id,
        ]);
    }
}

<?php

namespace App\Filament\Resources\ReturnedAccommodationResource\Pages;

use App\Filament\Resources\PatientResource;
use App\Filament\Resources\ReturnedAccommodationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReturnedAccommodation extends CreateRecord
{
    protected static string $resource = ReturnedAccommodationResource::class;
    protected function getRedirectUrl(): string
    {
        return PatientResource::getUrl('view', [
            'record' => $this->record->patient_id,
        ]);
    }
}

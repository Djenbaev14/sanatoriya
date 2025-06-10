<?php

namespace App\Filament\Resources\MedicalHistoryResource\Pages;

use App\Filament\Resources\MedicalHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMedicalHistory extends CreateRecord
{
    protected static string $resource = MedicalHistoryResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

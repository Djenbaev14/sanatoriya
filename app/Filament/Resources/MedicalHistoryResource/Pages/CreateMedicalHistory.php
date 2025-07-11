<?php

namespace App\Filament\Resources\MedicalHistoryResource\Pages;

use App\Filament\Resources\MedicalHistoryResource;
use App\Filament\Resources\PatientResource;
use App\Models\InspectionDetail;
use App\Models\MedicalInspection;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMedicalHistory extends CreateRecord
{
    protected static string $resource = MedicalHistoryResource::class;
    // protected function getRedirectUrl(): string
    // {
    //     return MedicalHistoryResource::getUrl('view', [
    //         'record' => $this->record->id,
    //     ]);
    // }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

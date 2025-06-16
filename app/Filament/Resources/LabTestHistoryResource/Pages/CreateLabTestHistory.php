<?php

namespace App\Filament\Resources\LabTestHistoryResource\Pages;

use App\Filament\Resources\LabTestHistoryResource;
use App\Filament\Resources\PatientResource;
use App\Models\LabTestHistory;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateLabTestHistory extends CreateRecord
{
    protected static string $resource = LabTestHistoryResource::class;
    protected function getRedirectUrl(): string
    {
        return LabTestHistoryResource::getUrl('view', [
            'record' => $this->record->id,
        ]);
    }
}

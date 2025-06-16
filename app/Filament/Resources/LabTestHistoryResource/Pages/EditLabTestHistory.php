<?php

namespace App\Filament\Resources\LabTestHistoryResource\Pages;

use App\Filament\Resources\LabTestHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLabTestHistory extends EditRecord
{
    protected static string $resource = LabTestHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

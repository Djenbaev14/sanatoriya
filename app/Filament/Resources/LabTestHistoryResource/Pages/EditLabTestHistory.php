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
    protected function getRedirectUrl(): string
    {
        return LabTestHistoryResource::getUrl('view', [
            'record' => $this->record->id,
        ]);
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['status_payment_id'] = 1;

        return $data;
    }
}

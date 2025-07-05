<?php

namespace App\Filament\Resources\AssignedProcedureResource\Pages;

use App\Filament\Resources\AssignedProcedureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssignedProcedure extends EditRecord
{
    protected static string $resource = AssignedProcedureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return AssignedProcedureResource::getUrl('view', [
            'record' => $this->record->id,
        ]);
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['status_payment_id'] = 1; 
        return $data;
    }
}

<?php

namespace App\Filament\Resources\AccommodationResource\Pages;

use App\Filament\Resources\AccommodationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccommodation extends EditRecord
{
    protected static string $resource = AccommodationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return AccommodationResource::getUrl('view', [
            'record' => $this->record->id,
        ]);
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // dd($data);
        $data['status_payment_id'] = 1;

        return $data;
    }
}

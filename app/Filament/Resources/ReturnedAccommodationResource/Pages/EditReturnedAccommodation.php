<?php

namespace App\Filament\Resources\ReturnedAccommodationResource\Pages;

use App\Filament\Resources\ReturnedAccommodationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReturnedAccommodation extends EditRecord
{
    protected static string $resource = ReturnedAccommodationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

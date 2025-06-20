<?php

namespace App\Filament\Resources\KassaLabTestResource\Pages;

use App\Filament\Resources\KassaLabTestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKassaLabTest extends EditRecord
{
    protected static string $resource = KassaLabTestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

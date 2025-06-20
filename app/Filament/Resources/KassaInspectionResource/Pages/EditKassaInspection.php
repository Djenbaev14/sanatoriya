<?php

namespace App\Filament\Resources\KassaInspectionResource\Pages;

use App\Filament\Resources\KassaInspectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKassaInspection extends EditRecord
{
    protected static string $resource = KassaInspectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

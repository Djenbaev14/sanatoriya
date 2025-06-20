<?php

namespace App\Filament\Resources\KassaProcedureResource\Pages;

use App\Filament\Resources\KassaProcedureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKassaProcedure extends EditRecord
{
    protected static string $resource = KassaProcedureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

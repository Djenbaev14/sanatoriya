<?php

namespace App\Filament\Resources\KassaBalanceResource\Pages;

use App\Filament\Resources\KassaBalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKassaBalance extends EditRecord
{
    protected static string $resource = KassaBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

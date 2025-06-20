<?php

namespace App\Filament\Resources\KassaKoykaResource\Pages;

use App\Filament\Resources\KassaKoykaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKassaKoyka extends EditRecord
{
    protected static string $resource = KassaKoykaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

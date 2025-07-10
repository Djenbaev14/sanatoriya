<?php

namespace App\Filament\Resources\CasheBoxSessionResource\Pages;

use App\Filament\Resources\CasheBoxSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCasheBoxSession extends EditRecord
{
    protected static string $resource = CasheBoxSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

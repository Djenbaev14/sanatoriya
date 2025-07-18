<?php

namespace App\Filament\Resources\MkbResource\Pages;

use App\Filament\Resources\MkbResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMkb extends EditRecord
{
    protected static string $resource = MkbResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

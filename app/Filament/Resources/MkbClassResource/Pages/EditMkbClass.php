<?php

namespace App\Filament\Resources\MkbClassResource\Pages;

use App\Filament\Resources\MkbClassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMkbClass extends EditRecord
{
    protected static string $resource = MkbClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

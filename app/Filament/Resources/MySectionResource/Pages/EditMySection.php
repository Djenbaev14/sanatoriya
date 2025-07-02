<?php

namespace App\Filament\Resources\MySectionResource\Pages;

use App\Filament\Resources\MySectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMySection extends EditRecord
{
    protected static string $resource = MySectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

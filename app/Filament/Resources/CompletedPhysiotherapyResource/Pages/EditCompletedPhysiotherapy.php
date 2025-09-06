<?php

namespace App\Filament\Resources\CompletedPhysiotherapyResource\Pages;

use App\Filament\Resources\CompletedPhysiotherapyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompletedPhysiotherapy extends EditRecord
{
    protected static string $resource = CompletedPhysiotherapyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

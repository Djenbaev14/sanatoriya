<?php

namespace App\Filament\Resources\CasheBoxSessionResource\Pages;

use App\Filament\Resources\CasheBoxSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCasheBoxSessions extends ListRecords
{
    protected static string $resource = CasheBoxSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

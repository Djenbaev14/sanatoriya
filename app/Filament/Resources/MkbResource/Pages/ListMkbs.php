<?php

namespace App\Filament\Resources\MkbResource\Pages;

use App\Filament\Resources\MkbResource;
use App\Imports\MkbImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMkbs extends ListRecords
{
    protected static string $resource = MkbResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->color("primary")
                ->use(MkbImport::class),
            // Actions\CreateAction::make(),
        ];
    }
}

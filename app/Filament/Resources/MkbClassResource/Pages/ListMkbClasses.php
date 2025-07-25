<?php

namespace App\Filament\Resources\MkbClassResource\Pages;

use App\Filament\Resources\MkbClassResource;
use App\Imports\MkbClassImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMkbClasses extends ListRecords
{
    protected static string $resource = MkbClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->color("primary")
                ->use(MkbClassImport::class),
        ];
    }
}

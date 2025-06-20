<?php

namespace App\Filament\Resources\KassaKoykaResource\Pages;

use App\Filament\Resources\KassaKoykaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKassaKoykas extends ListRecords
{
    protected static string $resource = KassaKoykaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

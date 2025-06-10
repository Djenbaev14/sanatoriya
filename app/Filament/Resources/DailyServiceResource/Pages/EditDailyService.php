<?php

namespace App\Filament\Resources\DailyServiceResource\Pages;

use App\Filament\Resources\DailyServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDailyService extends EditRecord
{
    protected static string $resource = DailyServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

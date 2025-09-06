<?php

namespace App\Filament\Resources\ProcedureSessionResource\Pages;

use App\Filament\Resources\ProcedureSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProcedureSession extends EditRecord
{
    protected static string $resource = ProcedureSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

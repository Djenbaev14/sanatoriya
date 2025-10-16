<?php

namespace App\Filament\Resources\FunctionDiagnosticResource\Pages;

use App\Filament\Resources\FunctionDiagnosticResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFunctionDiagnostic extends EditRecord
{
    protected static string $resource = FunctionDiagnosticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\DepartmentInspectionResource\Pages;

use App\Filament\Resources\DepartmentInspectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepartmentInspection extends EditRecord
{
    protected static string $resource = DepartmentInspectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

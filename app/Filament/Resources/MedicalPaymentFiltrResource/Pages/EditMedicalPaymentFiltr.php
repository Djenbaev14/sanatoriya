<?php

namespace App\Filament\Resources\MedicalPaymentFiltrResource\Pages;

use App\Filament\Resources\MedicalPaymentFiltrResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedicalPaymentFiltr extends EditRecord
{
    protected static string $resource = MedicalPaymentFiltrResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

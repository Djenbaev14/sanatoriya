<?php

namespace App\Filament\Resources\MedicalPaymentResource\Pages;

use App\Filament\Resources\MedicalPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedicalPayment extends EditRecord
{
    protected static string $resource = MedicalPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\PaymentProcedureResource\Pages;

use App\Filament\Resources\PaymentProcedureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentProcedure extends EditRecord
{
    protected static string $resource = PaymentProcedureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

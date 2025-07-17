<?php

namespace App\Filament\Resources\PaymentProcedureResource\Pages;

use App\Filament\Resources\PaymentProcedureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentProcedures extends ListRecords
{
    protected static string $resource = PaymentProcedureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

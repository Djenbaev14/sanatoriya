<?php

namespace App\Filament\Resources\PaymentLabTestResource\Pages;

use App\Filament\Resources\PaymentLabTestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentLabTests extends ListRecords
{
    protected static string $resource = PaymentLabTestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

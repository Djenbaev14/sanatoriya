<?php

namespace App\Filament\Resources\MedicalPaymentFiltrResource\Pages;

use App\Filament\Resources\MedicalPaymentFiltrResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedicalPaymentFiltrs extends ListRecords
{
    protected static string $resource = MedicalPaymentFiltrResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

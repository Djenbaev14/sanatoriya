<?php

namespace App\Filament\Resources\MedicalPaymentResource\Pages;

use App\Filament\Resources\MedicalPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedicalPayments extends ListRecords
{
    protected static string $resource = MedicalPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    public static function getRecordTitle($record): ?string
    {
        $history = $record->medicalHistory; // payment.medical_history_id orqali bog‘langan bo‘lsa

        if (!$history) return 'Журнал оплат'; // fallback

        return 'Журнал оплат №' . $history->number . ' - ' . ($history->patient->full_name ?? 'Nomaʼlum');
    }
}

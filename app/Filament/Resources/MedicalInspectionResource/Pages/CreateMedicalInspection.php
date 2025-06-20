<?php

namespace App\Filament\Resources\MedicalInspectionResource\Pages;

use App\Filament\Resources\MedicalInspectionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMedicalInspection extends CreateRecord
{
    protected static string $resource = MedicalInspectionResource::class;
    
    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $data = $this->setPaymentStatus($data);
    //     return $data;
    // }

    // private function setPaymentStatus(array $data): array
    // {
    //     if (empty($data['inspectionDetails']) || count($data['inspectionDetails']) === 0) {
    //         $data['status_payment_id'] = 3; // to‘langan
    //     } else {
    //         $data['status_payment_id'] = 1; // to‘lanmagan
    //     }
    //     return $data;
    // }
    protected function getRedirectUrl(): string
    {
        return MedicalInspectionResource::getUrl('view', [
            'record' => $this->record->id,
        ]);
    }
}

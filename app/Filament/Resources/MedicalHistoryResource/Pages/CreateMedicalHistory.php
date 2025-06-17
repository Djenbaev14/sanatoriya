<?php

namespace App\Filament\Resources\MedicalHistoryResource\Pages;

use App\Filament\Resources\MedicalHistoryResource;
use App\Filament\Resources\PatientResource;
use App\Models\InspectionDetail;
use App\Models\MedicalInspection;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMedicalHistory extends CreateRecord
{
    protected static string $resource = MedicalHistoryResource::class;
    protected function getRedirectUrl(): string
    {
        return PatientResource::getUrl('view', [
            'record' => $this->record->patient_id,
        ]);
    }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Inspection details'ni allab, asosiy formadan olib tashlash
        $inspectionDetails = $data['inspectionDetails'] ?? [];
        unset($data['inspectionDetails']);
        
        // Status payment ID'ni saqlash
        $statusPaymentId = $data['status_payment_id'] ?? 1;
        unset($data['status_payment_id']);
        
        // Inspection details'ni keyinroq ishlatish uchun saqlash
        $this->inspectionDetails = $inspectionDetails;
        $this->statusPaymentId = $statusPaymentId;

        return $data;
    }
    protected function handleRecordCreation(array $data): Model
    {
        // Avval medical history'ni yaratish
        $medicalHistory = static::getModel()::create($data);

        // Medical inspection yaratish
        $medicalInspection = MedicalInspection::create([
            'patient_id' => $medicalHistory->patient_id,
            'medical_history_id' => $medicalHistory->id,
            'status_payment_id' => $this->statusPaymentId,
        ]);

        // Inspection details yaratish
        if (!empty($this->inspectionDetails)) {
            foreach ($this->inspectionDetails as $detail) {
                InspectionDetail::create([
                    'medical_inspection_id' => $medicalInspection->id,
                    'inspection_id' => $detail['inspection_id'],
                    'price' => $detail['price'],
                ]);
            }
        }

        return $medicalHistory;
    }
    
    // Vaqtinchalik saqlash uchun property'lar
    protected array $inspectionDetails = [];
    protected int $statusPaymentId = 1;
}

<?php

namespace App\Filament\Resources\AccommodationResource\Pages;

use App\Filament\Resources\AccommodationResource;
use App\Models\Accommodation;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAccommodation extends CreateRecord
{
    protected static string $resource = AccommodationResource::class;
    protected function getRedirectUrl(): string
    {
        return AccommodationResource::getUrl('view', [
            'record' => $this->record->id,
        ]);
        
    }
    protected function afterCreate(): void
{
    if (
        $this->data['has_accomplice'] &&
        !empty($this->data['accomplice_patient_id'])
    ) {
        \App\Models\Accommodation::create([
            'main_accommodation_id' => $this->record->id,
            'patient_id' => $this->data['accomplice_patient_id'],
            'created_id' => $this->data['created_id'],
            'main_patient_id' => $this->record->patient_id,
            'bed_id' => $this->data['accomplice_bed_id'],
            'medical_history_id' => 20,
            'tariff_id' => $this->data['accomplice_tariff_id'],
            'tariff_price' => $this->data['accomplice_tariff_price'],
            'meal_type_id' => $this->data['accomplice_meal_type_id'],
            'meal_price' => $this->data['accomplice_meal_price'],
            'is_accomplice' => true,
            'ward_id' => $this->data['accomplice_ward_id'],
            'admission_date' => $this->data['accomplice_admission_date'],
            'discharge_date' => $this->data['accomplice_discharge_date'],
        ]);
    }
}
//     protected function mutateFormDataBeforeCreate(array $data): array
// {
//     // Asosiy bemor accommodation
//     $main = Accommodation::create([
//         'patient_id' => $data['patient_id'],
//         'created_id' => $data['created_id'],
//         'medical_history_id' => $data['medical_history_id'],
//         'bed_id' => $data['bed_id'],
//         'ward_id' => $data['ward_id'],
//         'tariff_id' => $data['tariff_id'],
//         'meal_type_id' => $data['meal_type_id'],
//         'admission_date' => $data['admission_date'],
//         'discharge_date' => $data['discharge_date'],
//         'is_accomplice' => false,
//     ]);

//     // Qarovchi bo‘lsa uni ham yaratamiz
//     if (!empty($data['has_accomplice']) && !empty($data['accomplice_patient_id'])) {
//         Accommodation::create([
//             'patient_id' => $data['accomplice_patient_id'],
//             'main_patient_id' => $data['patient_id'],
//             'bed_id' => $data['accomplice_bed_id'],
//             'ward_id' => $data['accomplice_ward_id'],
//             'tariff_id' => $data['accomplice_tariff_id'],
//             'meal_type_id' => $data['accomplice_meal_type_id'],
//             'admission_date' => $data['accomplice_admission_date'],
//             'discharge_date' => $data['accomplice_discharge_date'],
//             'is_accomplice' => true,
//         ]);
//     }

//     // Boshqa qiymatlar asl yozuvga qaytmaydi (model ochilmaydi)
//     return $data;
// }
}

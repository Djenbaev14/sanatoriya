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
                'ward_day' => $this->data['accomplice_ward_day'],
                'meal_day' => $this->data['accomplice_meal_day'],
                'admission_date' => $this->data['accomplice_admission_date'],
                'discharge_date' => $this->data['accomplice_discharge_date'],
            ]);
        }
    }
}

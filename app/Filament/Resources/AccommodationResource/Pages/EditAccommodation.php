<?php

namespace App\Filament\Resources\AccommodationResource\Pages;

use App\Filament\Resources\AccommodationResource;
use App\Models\Accommodation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccommodation extends EditRecord
{
    protected static string $resource = AccommodationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return AccommodationResource::getUrl('view', [
            'record' => $this->record->id,
        ]);
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // dd($data);
        $data['status_payment_id'] = 1;
        $main = $this->record; // asosiy accommodation yozuv

        // Qarovchi bo‘lsa
        if ($this->data['has_accomplice'] && !empty($this->data['accomplice_patient_id'])) {
            $accomplice = \App\Models\Accommodation::where('main_accommodation_id', $main->id)->first();

            if ($accomplice) {
                // Mavjud bo‘lsa, yangilaymiz
                $accomplice->update([
                    'patient_id' => $this->data['accomplice_patient_id'],
                    'main_patient_id' => $main->patient_id,
                    'bed_id' => $this->data['accomplice_bed_id'],
                    'tariff_id' => $this->data['accomplice_tariff_id'],
                    'tariff_price' => $this->data['accomplice_tariff_price'],
                    'meal_type_id' => $this->data['accomplice_meal_type_id'],
                    'meal_price' => $this->data['accomplice_meal_price'],
                    'is_accomplice' => true,
                    'ward_id' => $this->data['accomplice_ward_id'],
                    'admission_date' => $this->data['accomplice_admission_date'],
                    'discharge_date' => $this->data['accomplice_discharge_date'],
                    'status_payment_id' => 1,
                ]);
            } else {
                // Bo‘lmasa — yangi accommodation (qarovchi uchun)
                \App\Models\Accommodation::create([
                    'main_accommodation_id' => $main->id,
                    'patient_id' => $this->data['accomplice_patient_id'],
                    'main_patient_id' => $main->patient_id,
                    'bed_id' => $this->data['accomplice_bed_id'],
                    'tariff_id' => $this->data['accomplice_tariff_id'],
                    'tariff_price' => $this->data['accomplice_tariff_price'],
                    'meal_type_id' => $this->data['accomplice_meal_type_id'],
                    'meal_price' => $this->data['accomplice_meal_price'],
                    'is_accomplice' => true,
                    'ward_id' => $this->data['accomplice_ward_id'],
                    'admission_date' => $this->data['accomplice_admission_date'],
                    'discharge_date' => $this->data['accomplice_discharge_date'],
                    'status_payment_id' => 1,
                ]);
            }
        } else {
            // Qarovchi yo‘q qilib qo‘yilgan bo‘lsa — mavjudini o‘chir
            \App\Models\Accommodation::where('main_accommodation_id', $main->id)->delete();
        }

        return $data;
    }
    public function mutateFormDataBeforeFill(array $data): array
    {
        $record = Accommodation::with('partner')->find($data['id'] ?? null);

        if ($record && $record->partner) {
            $partner = $record->partner;

            $data['has_accomplice'] = true;
            $data['accomplice_patient_id'] = $partner->patient_id;
            $data['accomplice_tariff_id'] = $partner->tariff_id;
            $data['accomplice_tariff_price'] = $partner->tariff_price;
            $data['accomplice_meal_type_id'] = $partner->meal_type_id;
            $data['accomplice_meal_price'] = $partner->meal_price;
            $data['accomplice_bed_id'] = $partner->bed_id;
            $data['accomplice_ward_id'] = $partner->ward_id;
            $data['accomplice_admission_date'] = $partner->admission_date;
            $data['accomplice_discharge_date'] = $partner->discharge_date;
        }

        return $data;
    }
}

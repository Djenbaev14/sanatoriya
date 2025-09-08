<?php

namespace App\Filament\Resources\PatientResource\Pages;

use App\Filament\Resources\PatientResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreatePatient extends CreateRecord
{
    protected static string $resource = PatientResource::class;
    protected function authorizeAccess(): void
    {
        abort_unless(auth()->user()->can('создать больной'), 403);
    }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info( $data['photo']);
        if (isset($data['photo']) && str_starts_with($data['photo'], 'data:image')) {
            $data['photo'] = $this->saveBase64Image($data['photo']);
        }

        return $data;
    }

    private function saveBase64Image(string $base64): string
    {
        $image = str_replace('data:image/png;base64,', '', $base64);
        $image = str_replace(' ', '+', $image);
        $fileName = 'patients/' . uniqid() . '.png';
        \Storage::disk('public')->put($fileName, base64_decode($image));
        return $fileName;
    }
}

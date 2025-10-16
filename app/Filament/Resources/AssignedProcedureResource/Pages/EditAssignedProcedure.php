<?php

namespace App\Filament\Resources\AssignedProcedureResource\Pages;

use App\Filament\Resources\AssignedProcedureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssignedProcedure extends EditRecord
{
    protected static string $resource = AssignedProcedureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    // protected function getRedirectUrl(): string
    // {
    //     return AssignedProcedureResource::getUrl('view', [
    //         'record' => $this->record->id,
    //     ]);
    // }
    protected function afterSave(): void
    {
        $now = now();
        $formData = $this->data; // formdan kelgan inputlar
        $record = $this->record;

        foreach ($formData['procedureDetails'] ?? [] as $detailData) {
            $executorId = $detailData['executor_id'] ?? null;
            $timeId = $detailData['time_id'] ?? null;
            $sessions = (int) ($detailData['sessions'] ?? 0);

            // mavjud detailni topamiz
            $detail = $record->procedureDetails()
                ->where('procedure_id', $detailData['procedure_id'])
                ->first();

            if (! $detail) {
                continue;
            }

            // mavjud seanslar sonini olamiz
            $existingSessions = $detail->procedureSessions()->orderBy('session_date')->get();
            $existingCount = $existingSessions->count();

            $startDate = $now->copy();
            if ($now->hour >= 13) {
                $startDate->addDay();
            }

            // 🔹 AGAR yangi son kattaroq bo‘lsa — qo‘shimcha seanslar yaratamiz
            if ($sessions > $existingCount) {
                for ($i = $existingCount; $i < $sessions; $i++) {
                    $sessionDate = $startDate->copy()->addDays($i);
                    $detail->procedureSessions()->create([
                        'assigned_procedure_id' => $record->id,
                        'procedure_id'          => $detail->procedure_id,
                        'session_date'          => $sessionDate->toDateString(),
                        'time_id'               => $timeId,
                        'executor_id'           => $executorId,
                    ]);
                }
            }

            // 🔹 AGAR yangi son kichik bo‘lsa — ortiqcha seanslarni o‘chirib tashlaymiz
            elseif ($sessions < $existingCount) {
                $detail->procedureSessions()
                    ->orderByDesc('session_date')
                    ->take($existingCount - $sessions)
                    ->delete();
            }

            // 🔹 Qolgan seanslarni yangilaymiz (agar executor yoki time o‘zgargan bo‘lsa)
            $detail->procedureSessions()->update([
                'executor_id' => $executorId,
                'time_id'     => $timeId,
            ]);
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['status_payment_id'] = 1; 
        return $data;
    }
}

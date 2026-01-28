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

        $detailsData = collect($formData['procedureDetails'] ?? [])
            ->filter(fn ($d) => !empty($d['procedure_id']) && isset($d['sessions']));

        if ($detailsData->isEmpty()) {
            return;
        }

        /* 1️⃣ ProcedureDetail’larni oldindan olib qo‘yamiz */
        $procedureDetails = $record->procedureDetails()
            ->whereIn('procedure_id', $detailsData->pluck('procedure_id'))
            ->withCount('procedureSessions')
            ->get()
            ->keyBy('procedure_id');

        $startDate = $now->copy();
        if ($now->hour >= 13) {
            $startDate->addDay();
        }

        foreach ($detailsData as $detailData) {

            $detail = $procedureDetails->get($detailData['procedure_id']);
            if (! $detail) {
                continue;
            }

            $executorId = $detailData['executor_id'] ?? null;
            $timeId     = $detailData['time_id'] ?? null;
            $sessions   = (int) $detailData['sessions'];

            $existingCount = $detail->procedure_sessions_count;
            $diff = $sessions - $existingCount;

            /* 🔹 QO‘SHISH KERAK */
            if ($diff > 0) {
                $rows = [];

                for ($i = 0; $i < $diff; $i++) {
                    $rows[] = [
                        'assigned_procedure_id' => $record->id,
                        'procedure_id'          => $detail->procedure_id,
                        'session_date'          => $startDate->copy()->addDays($existingCount + $i)->toDateString(),
                        'executor_id'           => $executorId,
                        'time_id'               => $timeId,
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ];
                }

                \App\Models\ProcedureSession::insert($rows); // ✅ 1 query
            }

            /* 🔹 O‘CHIRISH KERAK */
            elseif ($diff < 0) {
                $detail->procedureSessions()
                    ->orderByDesc('session_date')
                    ->limit(abs($diff))
                    ->delete(); // ✅ 1 query
            }

            /* 🔹 QOLGANLARNI YANGILASH */
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

<?php

namespace App\Filament\Resources\AssignedProcedureResource\Pages;

use App\Filament\Resources\AssignedProcedureResource;
use App\Filament\Resources\PatientResource;
use App\Models\ProcedureDetail;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateAssignedProcedure extends CreateRecord
{
    protected static string $resource = AssignedProcedureResource::class;
    protected function getRedirectUrl(): string
    {
        return AssignedProcedureResource::getUrl('view', [
            'record' => $this->record->id,
        ]);
    }
    

    protected function afterCreate(): void
    {
        $now = now();
        $formData = $this->data;

        // assigned_procedure dan medical_history_id orqali accommodationni topamiz
        $accommodation = \App\Models\Accommodation::where('medical_history_id', $this->record->medical_history_id)->first();
        if (! $accommodation) {
            return;
        }

        $startDate = \Carbon\Carbon::parse($accommodation->admission_date);

        $detailsData = collect($formData['procedureDetails'] ?? [])
            ->filter(fn ($d) => !empty($d['procedure_id']));

        if ($detailsData->isEmpty()) {
            return;
        }

        /* 1️⃣ Kerakli procedure ID lar */
        $procedureIds = $detailsData->pluck('procedure_id')->unique();

        /* 2️⃣ Faqat kerakli procedurelarni oldindan yuklash */
        $procedures = \App\Models\Procedure::whereIn('id', $procedureIds)
            ->where('is_treatment', 0)
            ->where('is_operation', 0)
            ->get()
            ->keyBy('id');

        /* 3️⃣ ProcedureDetail + sessions_count */
        $procedureDetails = $this->record->procedureDetails()
            ->withCount('procedureSessions')
            ->whereIn('procedure_id', $procedures->keys())
            ->get()
            ->keyBy('procedure_id');

        $rows = [];

        foreach ($detailsData as $detailData) {

            $procedureId = $detailData['procedure_id'];
            $procedure = $procedures->get($procedureId);

            if (! $procedure) {
                continue;
            }

            $detail = $procedureDetails->get($procedureId);
            if (! $detail) {
                continue;
            }

            $existingCount = $detail->procedure_sessions_count;
            $needCount = max(0, $detail->sessions - $existingCount);

            for ($i = 0; $i < $needCount; $i++) {
                $rows[] = [
                    'assigned_procedure_id' => $this->record->id,
                    'procedure_id'          => $procedureId,
                    'session_date'          => $startDate->copy()->addDays($existingCount + $i)->toDateString(),
                    'time_id'               => $detailData['time_id'] ?? null,
                    'executor_id'           => $detailData['executor_id'] ?? null,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ];
            }
        }

        /* 4️⃣ 1 ta query bilan yozish */
        if (! empty($rows)) {
            \App\Models\ProcedureSession::insert($rows);
        }

    }




}

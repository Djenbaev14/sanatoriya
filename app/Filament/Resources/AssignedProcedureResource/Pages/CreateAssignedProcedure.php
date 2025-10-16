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

        foreach ($formData['procedureDetails'] ?? [] as $detailData) {
            $executorId = $detailData['executor_id'] ?? null;
            $timeId = $detailData['time_id'] ?? null;
            $procedureId = $detailData['procedure_id'] ?? null;

            if (! $procedureId) {
                continue;
            }

            // faqat is_treatment = 0 va is_operation = 0 bo‘lgan procedurelar uchun
            $procedure = \App\Models\Procedure::find($procedureId);
            if (! $procedure || $procedure->is_treatment != 0 || $procedure->is_operation != 0) {
                continue;
            }

            $detail = $this->record->procedureDetails()
                ->where('procedure_id', $procedureId)
                ->first();

            if ($detail) {
                $existingCount = $detail->procedureSessions()->count();

                if ($existingCount < $detail->sessions) {
                    for ($i = $existingCount; $i < $detail->sessions; $i++) {
                        $sessionDate = $startDate->copy()->addDays($i);

                        $detail->procedureSessions()->create([
                            'assigned_procedure_id' => $this->record->id,
                            'procedure_id'          => $procedureId,
                            'session_date'          => $sessionDate->toDateString(),
                            'time_id'               => $timeId,
                            'executor_id'           => $executorId,
                        ]);
                    }
                }
            }
        }
    }




}

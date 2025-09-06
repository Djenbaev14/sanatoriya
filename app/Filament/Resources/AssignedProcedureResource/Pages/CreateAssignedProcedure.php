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
        $now = Carbon::now();

        foreach ($this->record->procedureDetails as $detail) {
            if (!$detail->executor_id) {
                $existingCount = $detail->procedureSessions()->count();

                // Boshlanish sanasini aniqlash
                $startDate = $now->copy();
                if ($now->hour >= 13) {
                    $startDate->addDay(); // ertangi kundan boshlanadi
                }

                if ($existingCount < $detail->sessions) {
                    for ($i = $existingCount; $i < $detail->sessions; $i++) {
                        $sessionDate = $startDate->copy()->addDays($i);

                        $detail->procedureSessions()->create([
                            'assigned_procedure_id' => $this->record->id,
                            'procedure_id'          => $detail->procedure_id,
                            'session_date'          => $sessionDate->toDateString(),
                        ]);
                    }
                } elseif ($existingCount > $detail->sessions) {
                    $detail->procedureSessions()
                        ->latest()
                        ->take($existingCount - $detail->sessions)
                        ->delete();
                }
            }
        }
    }

}

<?php

namespace App\Filament\Resources\PatientsForPhysiotherapyResource\Pages;

use App\Filament\Resources\PatientsForPhysiotherapyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPatientsForPhysiotherapies extends ListRecords
{
    protected static string $resource = PatientsForPhysiotherapyResource::class;

    
    protected function getHeaderActions(): array
    {
        $userId = auth()->id();

        // auth userga tegishli sessionlarni olish
        $sessions = \App\Models\ProcedureSession::query()
            ->with('procedure') // aloqadagi nomi bo‘lishi kerak
            ->whereHas('procedureDetail', fn ($q) => $q->where('executor_id', $userId))
            ->where('is_completed', false)
            ->get();

        // Guruhlash: procedure bo‘yicha
        $grouped = $sessions->groupBy('procedure_id');

        $actions = [];

        foreach ($grouped as $procedureId => $items) {
            $procedureName = $items->first()->procedure->name ?? '—';

            $bugun = $items->where('session_date', today()->toDateString())->count();
            $ertaga = $items->where('session_date', \Carbon\Carbon::tomorrow()->toDateString())->count();
            $otibKetgan = $items->where('session_date', '<', today()->toDateString())->count();

            $label = "{$procedureName}: 
                Сегодня - {$bugun}, 
                Завтра - {$ertaga}, 
                Просроченный - {$otibKetgan}";

            $actions[] = \Filament\Actions\Action::make("procedure-{$procedureId}")
                ->label($label)
                ->color('gray')
                ->disabled();
        }

        return $actions;
    }

}

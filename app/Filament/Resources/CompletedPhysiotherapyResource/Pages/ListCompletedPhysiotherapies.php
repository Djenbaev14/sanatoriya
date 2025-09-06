<?php

namespace App\Filament\Resources\CompletedPhysiotherapyResource\Pages;

use App\Filament\Resources\CompletedPhysiotherapyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompletedPhysiotherapies extends ListRecords
{
    protected static string $resource = CompletedPhysiotherapyResource::class;

    protected function getHeaderActions(): array
    {
        $userId = auth()->id();

        // auth userga tegishli sessionlarni olish
        $sessions = \App\Models\ProcedureSession::query()
            ->with('procedure') // aloqadagi nomi bo‘lishi kerak
            ->whereHas('procedureDetail', fn ($q) => $q->where('executor_id', $userId))
            ->where('is_completed', true)
            ->get();

        // Guruhlash: procedure bo‘yicha
        $grouped = $sessions->groupBy('procedure_id');

        $actions = [];

        foreach ($grouped as $procedureId => $items) {
            $procedureName = $items->first()->procedure->name ?? '—';

            $bugun = $items->where('completed_at', today()->toDateString())->count();
            $kecha = $items->where('completed_at', \Carbon\Carbon::yesterday()->toDateString())->count();
            $otibKetgan = $items->where('completed_at', '<', today()->toDateString())->count();

            $label = "{$procedureName}: 
                Сегодня - {$bugun}, 
                Вчера - {$kecha}, 
                Просроченный - {$otibKetgan}";

            $actions[] = \Filament\Actions\Action::make("procedure-{$procedureId}")
                ->label($label)
                ->color('gray')
                ->disabled();
        }

        return $actions;
    }
}

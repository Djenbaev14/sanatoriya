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

        $today = today()->toDateString();
        $yesterday = \Carbon\Carbon::yesterday()->toDateString();

        foreach ($grouped as $procedureId => $items) {

            $procedureName = optional($items->first()->procedure)->name ?? '—';

            $stats = $items->reduce(function ($carry, $item) use ($today, $yesterday) {

                $date = optional($item->completed_at)->toDateString();

                if ($date === $today) {
                    $carry['today']++;
                } elseif ($date === $yesterday) {
                    $carry['yesterday']++;
                } elseif ($date && $date < $today) {
                    $carry['expired']++;
                }

                return $carry;
            }, [
                'today' => 0,
                'yesterday' => 0,
                'expired' => 0,
            ]);

            $label = "{$procedureName}:
        Сегодня - {$stats['today']},
        Вчера - {$stats['yesterday']},
        Просроченный - {$stats['expired']}";

            $actions[] = \Filament\Actions\Action::make("procedure-{$procedureId}")
                ->label($label)
                ->color('gray')
                ->disabled();
        }


        return $actions;
    }
}

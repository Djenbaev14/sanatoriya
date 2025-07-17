<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Procedure;
use Illuminate\Database\Eloquent\Builder;

class TopProcedures extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Наиболее прибыльные процедуры';
    protected static ?int $sort = 1; // Dashboardda tartib

    protected function getTableQuery(): Builder
{
    return Procedure::query()
        ->join('procedure_payment_details', 'procedures.id', '=', 'procedure_payment_details.procedure_id')
        ->join('procedure_payments', 'procedure_payment_details.procedure_payment_id', '=', 'procedure_payments.id')
        ->join('payments', 'procedure_payments.payment_id', '=', 'payments.id')
        ->select(
            'procedures.id',
            'procedures.name',
            \DB::raw('SUM(procedure_payment_details.price * procedure_payment_details.sessions) as total_amount')
        )
        ->groupBy('procedures.id', 'procedures.name')
        ->orderByRaw('SUM(procedure_payment_details.price * procedure_payment_details.sessions) DESC');
}

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('rowNumber')
                ->label('№')
                ->rowIndex(), // Filamentda tartib raqam chiqaradi
            Tables\Columns\TextColumn::make('name')->label('Название процедуры'),
            Tables\Columns\TextColumn::make('total_amount')->label('Выручка (сум)')
                ->money('UZS', true),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\LabTest;
use Illuminate\Database\Eloquent\Builder;

class TopLabTests extends BaseWidget
{
    use InteractsWithPageFilters;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Самые высокооплачиваемые анализы';

    protected static ?int $sort = 2; // Dashboardda tartib
    protected function getTableQuery(): Builder
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        return LabTest::select('lab_tests.id', 'lab_tests.name')
            ->join('lab_test_payment_details', 'lab_tests.id', '=', 'lab_test_payment_details.lab_test_id')
            ->join('lab_test_payments', 'lab_test_payment_details.lab_test_payment_id', '=', 'lab_test_payments.id')
            ->join('payments', 'lab_test_payments.payment_id', '=', 'payments.id')
            ->when($startDate, fn ($query) => $query->whereDate('payments.created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate('payments.created_at', '<=', $endDate))
            ->selectRaw('SUM(lab_test_payment_details.price) as total_amount')
            ->groupBy('lab_tests.id', 'lab_tests.name')
            ->orderByDesc('total_amount');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('rowNumber')
                ->label('№')
                ->rowIndex(), // Filamentda tartib raqam chiqaradi
            Tables\Columns\TextColumn::make('name')->label('Название анализа'),
            Tables\Columns\TextColumn::make('total_amount')->label('Выручка (сум)')
                ->money('UZS', true),
        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->can('остаток в кассе');
    }
}

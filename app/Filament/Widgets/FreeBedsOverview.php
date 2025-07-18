<?php

namespace App\Filament\Widgets;

use App\Models\Ward;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;


class FreeBedsOverview extends BaseWidget
{

    protected static ?int $sort = 4; // Dashboardda tartib
    protected static ?string $heading = 'Койкы';

    protected function getTableQuery():builder
    {
        return Ward::withCount([
            'beds',
            'beds as available_beds_count' => fn ($q) => $q->availableBeds(),
        ]);
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->can('остаток в кассе');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')
                ->label('№')
                ->rowIndex(), // tartib raqami

            TextColumn::make('name')
                ->label('Палата'),

            TextColumn::make('beds_count')
                ->label('Кол Койка'),

            TextColumn::make('available_beds_count')
                ->label('Пустая койка')
                ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
        ];
    }
}

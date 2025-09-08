<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FreeBedsOverview;
use App\Filament\Widgets\PaymentStats;
use App\Filament\Widgets\SanatoriumStats;
use App\Filament\Widgets\TopLabTests;
use App\Filament\Widgets\TopProcedures;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        $user = auth()->user();

        // Agar foydalanuvchida "view dashboard" permission bo‘lmasa → hech qanday filtr ko‘rinmaydi
        if (! $user->can('view dashboard')) {
            return $form->schema([]);
        }
        
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Дата начала')
                            ->afterStateHydrated(fn ($component, $state) => 
                                $component->state($state ?: now()->startOfMonth()->toDateString())
                            )
                            ->maxDate(fn (Get $get) => $get('endDate') ?: now()),
                        DatePicker::make('endDate')
                            ->label('Дата окончания')
                            ->afterStateHydrated(fn ($component, $state) => 
                                $component->state($state ?: now()->toDateString())
                            )
                            ->minDate(fn (Get $get) => $get('startDate') ?: now())
                            ->maxDate(now()),
                    ])
                    ->columns(2),
            ]);
    }
    
    protected function getDefaultFilters(): array
    {
        return [
            'startDate' => now()->startOfMonth()->toDateString(),
            'endDate'   => now()->toDateString(),
        ];
    }
    public function getWidgets(): array
    {
        $user = auth()->user();

        if (! $user->can('view dashboard')) {
            // ❌ Permission yo‘q bo‘lsa, hech narsa ko‘rsatmaymiz
            return [];
        }
        return [
            PaymentStats::class,
            TopProcedures::class,
            TopLabTests::class, // Yangi mijozlar trend grafikasi
            FreeBedsOverview::class,
        ];
    }
}
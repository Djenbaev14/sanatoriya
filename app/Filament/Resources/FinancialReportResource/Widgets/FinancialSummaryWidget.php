<?php

namespace App\Filament\Resources\FinancialReportResource\Widgets;

use App\Models\MedicalHistory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Illuminate\Support\Facades\DB;

class FinancialSummaryWidget extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
{
    $baseQuery = $this->getPageTableQuery();

    $totals = [
        'ward' => (clone $baseQuery)->sum('total_ward_payment'),
        'meal' => (clone $baseQuery)->sum('total_meal_payment'),
        'medical' => (clone $baseQuery)->sum('total_medical_services_payment'),
        'ward_partner' => (clone $baseQuery)->sum('total_ward_payment_partner'),
        'meal_partner' => (clone $baseQuery)->sum('total_meal_payment_partner'),
    ];

    // ❗ getTotalCost() PHP method bo‘lgani uchun SQL’da hisoblaymiz
    $totals['contract'] = (clone $baseQuery)->selectRaw('
        COALESCE(total_ward_payment,0) +
        COALESCE(total_meal_payment,0) +
        COALESCE(total_medical_services_payment,0) +
        COALESCE(total_ward_payment_partner,0) +
        COALESCE(total_meal_payment_partner,0)
    ')->sum(DB::raw('(
        COALESCE(total_ward_payment,0) +
        COALESCE(total_meal_payment,0) +
        COALESCE(total_medical_services_payment,0) +
        COALESCE(total_ward_payment_partner,0) +
        COALESCE(total_meal_payment_partner,0)
    )'));

    $totalPaid = array_sum([
        $totals['ward'],
        $totals['meal'],
        $totals['medical'],
        $totals['ward_partner'],
        $totals['meal_partner'],
    ]);

    $recordsCount = (clone $baseQuery)->count();

    return [
        Stat::make('Umumiy to\'langan', $this->formatMoney($totalPaid))
            ->description($recordsCount . ' ta yozuv')
            ->descriptionIcon('heroicon-m-banknotes')
            ->color('success'),

        Stat::make('Shartnoma summasi', $this->formatMoney($totals['contract']))
            ->description('Umumiy shartnoma qiymati')
            ->descriptionIcon('heroicon-m-document-text')
            ->color('info'),

        Stat::make('Койка to\'lovlari', $this->formatMoney($totals['ward']))
            ->description('Asosiy койка xizmatlari')
            ->descriptionIcon('heroicon-m-home')
            ->color('primary'),

        Stat::make('Питание to\'lovlari', $this->formatMoney($totals['meal']))
            ->description('Asosiy ovqatlanish')
            ->descriptionIcon('heroicon-m-cake')
            ->color('warning'),

        Stat::make('Мед услуг to\'lovlari', $this->formatMoney($totals['medical']))
            ->description('Tibbiy xizmatlar')
            ->descriptionIcon('heroicon-m-heart')
            ->color('danger'),

        Stat::make('Койка (Уход)', $this->formatMoney($totals['ward_partner']))
            ->description('Hamrohlik койка')
            ->descriptionIcon('heroicon-m-user-group')
            ->color('gray'),

        Stat::make('Питание (Уход)', $this->formatMoney($totals['meal_partner']))
            ->description('Hamrohlik ovqatlanish')
            ->descriptionIcon('heroicon-m-users')
            ->color('indigo'),
    ];
}

    protected function getTablePage(): string
    {
        return \App\Filament\Resources\FinancialReportResource\Pages\ListFinancialReports::class;
    }

    protected function formatMoney(float $amount): string
    {
        return number_format($amount, 0, '.', ' ') . ' сум';
    }
}

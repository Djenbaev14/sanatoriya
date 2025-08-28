<?php

namespace App\Filament\Resources\FinancialReportResource\Widgets;

use App\Models\MedicalHistory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;

class FinancialSummaryWidget extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // Bu trait resource sahifasidan filtrlangan query beradi
        $query = $this->getPageTableQuery();

        $records = $query->get();

        $totals = [
            'ward' => 0,
            'meal' => 0,
            'medical' => 0,
            'ward_partner' => 0,
            'meal_partner' => 0,
            'contract' => 0,
        ];

        foreach ($records as $record) {
            $totals['ward'] += $record->total_ward_payment ?? 0;
            $totals['meal'] += $record->total_meal_payment ?? 0;
            $totals['medical'] += $record->total_medical_services_payment ?? 0;
            $totals['ward_partner'] += $record->total_ward_payment_partner ?? 0;
            $totals['meal_partner'] += $record->total_meal_payment_partner ?? 0;
            $totals['contract'] += $record->getTotalCost() ?? 0;
        }

        $totalPaid = $totals['ward'] + $totals['meal'] + $totals['medical'] +
                    $totals['ward_partner'] + $totals['meal_partner'];

        $recordsCount = $records->count();

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

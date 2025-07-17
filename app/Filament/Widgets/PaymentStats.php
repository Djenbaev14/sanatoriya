<?php

namespace App\Filament\Widgets;

use App\Models\AccommodationPayment;
use App\Models\LabTestPaymentDetail;
use App\Models\ProcedurePaymentDetail;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentStats extends BaseWidget
{
    
    protected static ?int $sort = 1; // Dashboardda tartib
    public ?string $filter = 'today';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Bugun',
            'this_week' => 'Shu hafta',
            'this_month' => 'Shu oy',
            'this_year' => 'Shu yil',
            'all_time' => 'Barcha vaqt',
        ];
    }

    protected function getStats(): array
    {
        $dateRange = $this->getDateRange();
        
        // Lab test payments statistics
        $labTestAmount = $this->getLabTestPayments($dateRange[0], $dateRange[1]);
        
        // Procedure payments statistics
        $procedureAmount = $this->getProcedurePayments($dateRange[0], $dateRange[1]);
        
        // Accommodation payments statistics (koyka va pitanie alohida)
        $accommodationAmounts = $this->getAccommodationPayments($dateRange[0], $dateRange[1]);
        $koykaAmount = $accommodationAmounts['koyka'];
        $pitanieAmount = $accommodationAmounts['pitanie'];
        $totalAccommodation = $koykaAmount + $pitanieAmount;
        
        // Total payments
        $totalAmount = $labTestAmount + $procedureAmount + $totalAccommodation;

        return [
            Stat::make('Общий доход', $this->formatCurrency($totalAmount))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Анализ (Лаборатория)', $this->formatCurrency($labTestAmount))
                ->color('info'),

            Stat::make('Лечение', $this->formatCurrency($procedureAmount))
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),

            Stat::make('Койка', $this->formatCurrency($koykaAmount))
                ->descriptionIcon('heroicon-m-home')
                ->color('primary'),

            Stat::make('Питание', $this->formatCurrency($pitanieAmount))
                ->descriptionIcon('heroicon-m-cake')
                ->color('gray'),
        ];
    }

    private function getDateRange(): array
    {
        return match ($this->filter) {
            'today' => [Carbon::today(), Carbon::today()->endOfDay()],
            'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'this_year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            'all_time' => [Carbon::create(2020, 1, 1), Carbon::now()],
            default => [Carbon::today(), Carbon::today()->endOfDay()],
        };
    }

    private function getLabTestPayments($startDate, $endDate): float
    {
        $query = LabTestPaymentDetail::query();

        return $query->selectRaw('SUM(sessions * price) as total')
            ->value('total') ?? 0;
    }

    private function getProcedurePayments($startDate, $endDate): float
    {
        $query = ProcedurePaymentDetail::query();

        return $query->selectRaw('SUM(sessions * price) as total')
            ->value('total') ?? 0;
    }

    private function getAccommodationPayments($startDate, $endDate): array
    {
        $query = AccommodationPayment::query();
        
        // Koyka uchun (tariff_price * ward_day)
        $koykaAmount = $query->selectRaw('SUM(tariff_price * COALESCE(ward_day, 0)) as total')
            ->value('total') ?? 0;
        
        // Pitanie uchun (meal_price * meal_day)
        $pitanieAmount = $query->selectRaw('SUM(meal_price * COALESCE(meal_day, 0)) as total')
            ->value('total') ?? 0;
        
        return [
            'koyka' => $koykaAmount,
            'pitanie' => $pitanieAmount,
        ];
    }

    private function formatCurrency(float $amount): string
    {
        return number_format($amount, 0, '.', ' ') . ' so\'m';
    }
}

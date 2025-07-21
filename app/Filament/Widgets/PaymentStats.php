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

    public static function canAccess(): bool
    {
        return auth()->user()?->can('остаток в кассе');
    }

    protected function getStats(): array
    {
        
        // Lab test payments statistics
        $labTestAmount = $this->getLabTestPayments();
        
        // Procedure payments statistics
        $procedureAmount = $this->getProcedurePayments();
        
        // Accommodation payments statistics (koyka va pitanie alohida)
        $accommodationAmounts = $this->getAccommodationPayments();
        $koykaAmount = $accommodationAmounts['koyka'];
        $pitanieAmount = $accommodationAmounts['pitanie'];
        $koykaAmountUxod = $accommodationAmounts['koykaUxod'];
        $pitanieAmountUxod = $accommodationAmounts['pitanieUxod'];
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
                
            Stat::make('Койка(Уход)', $this->formatCurrency($koykaAmountUxod))
                ->descriptionIcon('heroicon-m-home')
                ->color('primary'),

            Stat::make('Питание(Уход)', $this->formatCurrency($pitanieAmountUxod))
                ->descriptionIcon('heroicon-m-cake')
                ->color('gray'),
        ];
    }


    private function getLabTestPayments(): float
    {
        $query = LabTestPaymentDetail::query();

        return $query->selectRaw('SUM(sessions * price) as total')
            ->value('total') ?? 0;
    }

    private function getProcedurePayments(): float
    {
        $query = ProcedurePaymentDetail::query();

        return $query->selectRaw('SUM(sessions * price) as total')
            ->value('total') ?? 0;
    }

    private function getAccommodationPayments(): array
    {
        $query = AccommodationPayment::query()->whereNotNull('medical_history_id');
        $query1 = AccommodationPayment::query()->whereNull('medical_history_id');
        
        // Koyka uchun (tariff_price * ward_day)
        $koykaAmount = $query->selectRaw('SUM(tariff_price * COALESCE(ward_day, 0)) as total')
            ->value('total') ?? 0;
        
        // Pitanie uchun (meal_price * meal_day)
        $pitanieAmount = $query->selectRaw('SUM(meal_price * COALESCE(meal_day, 0)) as total')
            ->value('total') ?? 0;
            
            // Koyka uchun (tariff_price * ward_day)
        $koykaUxodAmount = $query1->selectRaw('SUM(tariff_price * COALESCE(ward_day, 0)) as total')
            ->value('total') ?? 0;
        
        // Pitanie uchun (meal_price * meal_day)
        $pitanieUxodAmount = $query1->selectRaw('SUM(meal_price * COALESCE(meal_day, 0)) as total')
            ->value('total') ?? 0;
        
        return [
            'koyka' => $koykaAmount,
            'pitanie' => $pitanieAmount,
            'koykaUxod' => $koykaUxodAmount,
            'pitanieUxod' => $pitanieUxodAmount,
        ];
    }

    private function formatCurrency(float $amount): string
    {
        return number_format($amount, 0, '.', ' ') . ' сум';
    }
}

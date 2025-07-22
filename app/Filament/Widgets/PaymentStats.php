<?php

namespace App\Filament\Widgets;

use App\Models\AccommodationPayment;
use App\Models\LabTestPaymentDetail;
use App\Models\ProcedurePaymentDetail;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Log;

class PaymentStats extends BaseWidget
{
    use InteractsWithPageFilters;
    
    protected static ?int $sort = 1; // Dashboardda tartib

    public static function canAccess(): bool
    {
        return auth()->user()?->can('остаток в кассе');
    }
    private function getDateRange(): array
    {
        $start = $this->filters['startDate'];
        $end = $this->filters['endDate'];

        return [
            'start' => $start ? Carbon::parse($start)->startOfDay() : null,
            'end' => $end ? Carbon::parse($end)->endOfDay() : null,
        ];
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
        $totalAccommodation = $koykaAmount + $pitanieAmount +$pitanieAmountUxod +$koykaAmountUxod;
        
        // Total payments
        $totalAmount = $labTestAmount + $procedureAmount + $totalAccommodation;
        // dd($this->getDateRange());
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
        $dates = $this->getDateRange();
        $query = LabTestPaymentDetail::query()
            ->join('lab_test_payments', 'lab_test_payment_details.lab_test_payment_id', '=', 'lab_test_payments.id')
            ->join('payments', 'lab_test_payments.payment_id', '=', 'payments.id');

        if ($dates['start'] && $dates['end']) {
            $query->whereBetween('payments.created_at', [$dates['start'], $dates['end']]);
        }
        return $query->selectRaw('SUM(sessions * price) as total')
            ->value('total') ?? 0;
    }

    private function getProcedurePayments(): float
    {
        $dates = $this->getDateRange();
        $query = ProcedurePaymentDetail::query()
            ->join('procedure_payments', 'procedure_payment_details.procedure_payment_id', '=', 'procedure_payments.id')
            ->join('payments', 'procedure_payments.payment_id', '=', 'payments.id');

        if ($dates['start'] && $dates['end']) {
            $query->whereBetween('payments.created_at', [$dates['start'], $dates['end']]);
        }
        return $query->selectRaw('SUM(sessions * price) as total')
            ->value('total') ?? 0;
    }

    private function getAccommodationPayments(): array
    {
        $dates = $this->getDateRange();

        $query = AccommodationPayment::query()
            ->join('payments', 'accommodation_payments.payment_id', '=', 'payments.id')
            ->whereNotNull('accommodation_payments.medical_history_id');
        $query1 = AccommodationPayment::query()
            ->join('payments', 'accommodation_payments.payment_id', '=', 'payments.id')
            ->whereNull('accommodation_payments.medical_history_id');

        if ($dates['start'] && $dates['end']) {
            $query->whereBetween('payments.created_at', [$dates['start'], $dates['end']]);
            $query1->whereBetween('payments.created_at', [$dates['start'], $dates['end']]);
        }

        $koykaAmount = $query->selectRaw('SUM(tariff_price * COALESCE(ward_day, 0)) as total')->value('total') ?? 0;
        $pitanieAmount = $query->selectRaw('SUM(meal_price * COALESCE(meal_day, 0)) as total')->value('total') ?? 0;

        $koykaUxodAmount = $query1->selectRaw('SUM(tariff_price * COALESCE(ward_day, 0)) as total')->value('total') ?? 0;
        $pitanieUxodAmount = $query1->selectRaw('SUM(meal_price * COALESCE(meal_day, 0)) as total')->value('total') ?? 0;

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

<?php

namespace App\Filament\Widgets;

use App\Models\Accommodation;
use App\Models\AccommodationPayment;
use App\Models\LabTestPaymentDetail;
use App\Models\MedicalHistory;
use App\Models\ProcedurePaymentDetail;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class MedicalHistoryPaymentsStats extends BaseWidget
{
    use InteractsWithPageFilters;
    
    protected static ?int $sort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('остаток в кассе');
    }

    private function getDateRange(): array
    {
        $filters = $this->filters ?? [];
        $start = $filters['startDate'] ?? $filters['start'] ?? now()->startOfMonth()->format('Y-m-d');
        $end = $filters['endDate'] ?? $filters['end'] ?? now()->endOfMonth()->format('Y-m-d');

        return [
            'start' => $start ? Carbon::parse($start)->startOfDay() : null,
            'end' => $end ? Carbon::parse($end)->endOfDay() : null,
        ];
    }

    protected function getStats(): array
    {
        // Medical histories bo'yicha filtr qilish (accommodations orqali)
        $medicalHistoryIds = $this->getFilteredMedicalHistoryIds();
        
        if (empty($medicalHistoryIds)) {
            return $this->getEmptyStats();
        }

        // Lab test payments statistics (medical_history_id bilan)
        $labTestAmount = $this->getLabTestPaymentsByMedicalHistory($medicalHistoryIds);
        
        // Procedure payments statistics (medical_history_id bilan)
        $procedureAmount = $this->getProcedurePaymentsByMedicalHistory($medicalHistoryIds);
        
        // Accommodation payments statistics
        $accommodationAmounts = $this->getAccommodationPaymentsByMedicalHistory($medicalHistoryIds);
        $koykaAmount = $accommodationAmounts['koyka'];
        $pitanieAmount = $accommodationAmounts['pitanie'];
        $koykaAmountUxod = $accommodationAmounts['koykaUxod'];
        $pitanieAmountUxod = $accommodationAmounts['pitanieUxod'];
        $totalAccommodation = $koykaAmount + $pitanieAmount + $pitanieAmountUxod + $koykaAmountUxod;
        
        // Total payments
        $totalAmount = $labTestAmount + $procedureAmount + $totalAccommodation;
        
        // Переходящий остаток hisoblanishi
        $carryOverBalance = $this->getCarryOverBalance($medicalHistoryIds);

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

            Stat::make('Переходящий остаток', $this->formatCurrency($carryOverBalance))
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('secondary'),
        ];
    }

    private function getFilteredMedicalHistoryIds(): array
    {
        $dates = $this->getDateRange();
        
        // Accommodations jadvalidan medical_history_id larni olamiz
        $query = Accommodation::query()
            ->whereNotNull('medical_history_id');

        if ($dates['start'] && $dates['end']) {
            $query->where(function ($q) use ($dates) {
                // Bemorlar shu muddat ichida yotgan bo'lishi kerak
                $q->where(function ($subQ) use ($dates) {
                    // Shu vaqt ichida kelganlar
                    $subQ->whereBetween('admission_date', [$dates['start'], $dates['end']]);
                })->orWhere(function ($subQ) use ($dates) {
                    // Yoki shu vaqt ichida chiqqanlar
                    $subQ->whereBetween('discharge_date', [$dates['start'], $dates['end']]);
                })->orWhere(function ($subQ) use ($dates) {
                    // Yoki bu muddat ichida yotib kelganlar (muddat ichida bo'lganlar)
                    $subQ->where('admission_date', '<=', $dates['start'])
                         ->where(function ($innerQ) use ($dates) {
                             $innerQ->where('discharge_date', '>=', $dates['end'])
                                    ->orWhereNull('discharge_date');
                         });
                });
            });
        }

        return $query->pluck('medical_history_id')->unique()->values()->toArray();
    }

    private function getLabTestPaymentsByMedicalHistory(array $medicalHistoryIds): float
    {
        $dates = $this->getDateRange();
        
        $query = LabTestPaymentDetail::query()
            ->join('lab_test_payments', 'lab_test_payment_details.lab_test_payment_id', '=', 'lab_test_payments.id')
            ->join('payments', 'lab_test_payments.payment_id', '=', 'payments.id')
            ->whereIn('payments.medical_history_id', $medicalHistoryIds);

        if ($dates['start'] && $dates['end']) {
            $query->whereBetween('payments.created_at', [$dates['start'], $dates['end']]);
        }

        return $query->selectRaw('SUM(sessions * price) as total')
            ->value('total') ?? 0;
    }

    private function getProcedurePaymentsByMedicalHistory(array $medicalHistoryIds): float
    {
        $dates = $this->getDateRange();
        
        $query = ProcedurePaymentDetail::query()
            ->join('procedure_payments', 'procedure_payment_details.procedure_payment_id', '=', 'procedure_payments.id')
            ->join('payments', 'procedure_payments.payment_id', '=', 'payments.id')
            ->whereIn('payments.medical_history_id', $medicalHistoryIds);

        if ($dates['start'] && $dates['end']) {
            $query->whereBetween('payments.created_at', [$dates['start'], $dates['end']]);
        }

        return $query->selectRaw('SUM(sessions * price) as total')
            ->value('total') ?? 0;
    }

    private function getAccommodationPaymentsByMedicalHistory(array $medicalHistoryIds): array
    {
        $dates = $this->getDateRange();

        // Medical history bilan bog'langan to'lovlar (asosiy bemorlar)
        $query = AccommodationPayment::query()
            ->join('payments', 'accommodation_payments.payment_id', '=', 'payments.id')
            ->whereIn('payments.medical_history_id', $medicalHistoryIds)
            ->whereNotNull('accommodation_payments.medical_history_id');

        // Medical history bilan bog'lanmagan to'lovlar (Uxod qiluvchilar)
        // Lekin payments.medical_history_id da bog'liq bo'lgan
        $query1 = AccommodationPayment::query()
            ->join('payments', 'accommodation_payments.payment_id', '=', 'payments.id')
            ->join('accommodations', 'accommodation_payments.medical_history_id', '=', 'accommodations.medical_history_id')
            ->whereIn('payments.medical_history_id', $medicalHistoryIds)
            ->whereNull('accommodation_payments.medical_history_id'); 

        if ($dates['start'] && $dates['end']) {
            $query->whereBetween('payments.created_at', [$dates['start'], $dates['end']]);
            $query1->whereBetween('payments.created_at', [$dates['start'], $dates['end']]);
        }

        $koykaAmount = $query->selectRaw('SUM(tariff_price * COALESCE(ward_day, 0)) as total')->value('total') ?? 0;
        $pitanieAmount = $query->selectRaw('SUM(meal_price * COALESCE(meal_day, 0)) as total')->value('total') ?? 0;

        $koykaUxodAmount = $query1->selectRaw('SUM(accommodation_payments.tariff_price * COALESCE(accommodation_payments.ward_day, 0)) as total')->value('total') ?? 0;
        $pitanieUxodAmount = $query1->selectRaw('SUM(accommodation_payments.meal_price * COALESCE(accommodation_payments.meal_day, 0)) as total')->value('total') ?? 0;

        return [
            'koyka' => $koykaAmount,
            'pitanie' => $pitanieAmount,
            'koykaUxod' => $koykaUxodAmount,
            'pitanieUxod' => $pitanieUxodAmount,
        ];
    }

    private function getCarryOverBalance(array $medicalHistoryIds): float
    {
        $dates = $this->getDateRange();
        
        if (!$dates['start']) {
            return 0;
        }

        // Переходящий остаток - bu davr boshida aktiv bo'lgan bemorlar
        $carryOverQuery = Accommodation::query()
            ->whereIn('medical_history_id', $medicalHistoryIds)
            ->where('admission_date', '<', $dates['start'])
            ->where(function ($q) use ($dates) {
                $q->where('discharge_date', '>', $dates['start'])
                  ->orWhereNull('discharge_date');
            });

        $carryOverHistoryIds = $carryOverQuery->pluck('medical_history_id')->unique()->values()->toArray();
        
        if (empty($carryOverHistoryIds)) {
            return 0;
        }

        // Bu bemorlarning davr boshigacha bo'lgan to'lovlari
        $carryOverAmount = 0;
        
        // Lab test payments
        $carryOverAmount += LabTestPaymentDetail::query()
            ->join('lab_test_payments', 'lab_test_payment_details.lab_test_payment_id', '=', 'lab_test_payments.id')
            ->join('payments', 'lab_test_payments.payment_id', '=', 'payments.id')
            ->whereIn('payments.medical_history_id', $carryOverHistoryIds)
            ->where('payments.created_at', '<', $dates['start'])
            ->selectRaw('SUM(sessions * price) as total')
            ->value('total') ?? 0;

        // Procedure payments
        $carryOverAmount += ProcedurePaymentDetail::query()
            ->join('procedure_payments', 'procedure_payment_details.procedure_payment_id', '=', 'procedure_payments.id')
            ->join('payments', 'procedure_payments.payment_id', '=', 'payments.id')
            ->whereIn('payments.medical_history_id', $carryOverHistoryIds)
            ->where('payments.created_at', '<', $dates['start'])
            ->selectRaw('SUM(sessions * price) as total')
            ->value('total') ?? 0;

        // Accommodation payments
        $carryOverAmount += AccommodationPayment::query()
            ->join('payments', 'accommodation_payments.payment_id', '=', 'payments.id')
            ->whereIn('payments.medical_history_id', $carryOverHistoryIds)
            ->where('payments.created_at', '<', $dates['start'])
            ->selectRaw('SUM((tariff_price * COALESCE(ward_day, 0)) + (meal_price * COALESCE(meal_day, 0))) as total')
            ->value('total') ?? 0;

        return $carryOverAmount;
    }

    private function getEmptyStats(): array
    {
        return [
            Stat::make('Общий доход', $this->formatCurrency(0))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Анализ (Лаборатория)', $this->formatCurrency(0))
                ->color('info'),

            Stat::make('Лечение', $this->formatCurrency(0))
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),

            Stat::make('Койка', $this->formatCurrency(0))
                ->descriptionIcon('heroicon-m-home')
                ->color('primary'),

            Stat::make('Питание', $this->formatCurrency(0))
                ->descriptionIcon('heroicon-m-cake')
                ->color('gray'),
                
            Stat::make('Койка(Уход)', $this->formatCurrency(0))
                ->descriptionIcon('heroicon-m-home')
                ->color('primary'),

            Stat::make('Питание(Уход)', $this->formatCurrency(0))
                ->descriptionIcon('heroicon-m-cake')
                ->color('gray'),

            Stat::make('Переходящий остаток', $this->formatCurrency(0))
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('secondary'),
        ];
    }

    private function formatCurrency(float $amount): string
    {
        return number_format($amount, 0, '.', ' ') . ' сум';
    }
}
<?php

namespace App\Filament\Resources\MedicalPaymentFiltrResource\Pages;

use App\Filament\Resources\MedicalPaymentFiltrResource;
use App\Filament\Widgets\MedicalHistoryPaymentsStats;
use App\Models\Accommodation;
use App\Models\AccommodationPayment;
use App\Models\LabTestPaymentDetail;
use App\Models\ProcedurePaymentDetail;
use Filament\Forms\Components\Group;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MedicalPayment extends Page
{
    protected static string $resource = MedicalPaymentFiltrResource::class;

    protected static string $view = 'filament.resources.payment-resource.medical-history-report';
    
    use InteractsWithFormActions;
    public $startDate;
    public $endDate;
    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
        $this->getStats();
    }
    
    public function updated($property)
    {
        $this->getStats();
    }
    public function getStats(): array
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

    public function getFilteredMedicalHistoryIds(): array
    {
        
        // Accommodations jadvalidan medical_history_id larni olamiz
        $query = Accommodation::query()
            ->whereNotNull('medical_history_id');

        if ($this->startDate && $this->endDate) {
            $query->where(function ($q) {
                // Bemorlar shu muddat ichida yotgan bo'lishi kerak
                $q->where(function ($subQ)  {
                    // Shu vaqt ichida kelganlar
                    $subQ->whereBetween('admission_date', [$this->startDate, $this->endDate]);
                })->orWhere(function ($subQ)  {
                    // Yoki shu vaqt ichida chiqqanlar
                    $subQ->whereBetween('discharge_date', [$this->startDate, $this->endDate]);
                })->orWhere(function ($subQ)  {
                    // Yoki bu muddat ichida yotib kelganlar (muddat ichida bo'lganlar)
                    $subQ->where('admission_date', '<=', $this->startDate)
                         ->where(function ($innerQ)  {
                             $innerQ->where('discharge_date', '>=', $this->endDate)
                                    ->orWhereNull('discharge_date');
                         });
                });
            });
        }

        return $query->pluck('medical_history_id')->unique()->values()->toArray();
    }

    public function getLabTestPaymentsByMedicalHistory(array $medicalHistoryIds): float
    {
        $query = LabTestPaymentDetail::query()
            ->join('lab_test_payments', 'lab_test_payment_details.lab_test_payment_id', '=', 'lab_test_payments.id')
            ->join('payments', 'lab_test_payments.payment_id', '=', 'payments.id')
            ->whereIn('payments.medical_history_id', $medicalHistoryIds);

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('payments.created_at', [$this->startDate, $this->endDate]);
        }

        return $query->selectRaw('SUM(sessions * price) as total')
            ->value('total') ?? 0;
    }

    public function getProcedurePaymentsByMedicalHistory(array $medicalHistoryIds): float
    {
        $query = ProcedurePaymentDetail::query()
            ->join('procedure_payments', 'procedure_payment_details.procedure_payment_id', '=', 'procedure_payments.id')
            ->join('payments', 'procedure_payments.payment_id', '=', 'payments.id')
            ->whereIn('payments.medical_history_id', $medicalHistoryIds);

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('payments.created_at', [$this->startDate, $this->endDate]);
        }

        return $query->selectRaw('SUM(sessions * price) as total')
            ->value('total') ?? 0;
    }

    public function getAccommodationPaymentsByMedicalHistory(array $medicalHistoryIds): array
    {
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

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('payments.created_at', [$this->startDate, $this->endDate]);
            $query1->whereBetween('payments.created_at', [$this->startDate, $this->endDate]);
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

    public function getCarryOverBalance(array $medicalHistoryIds): float
    {
        if (!$this->startDate) {
            return 0;
        }

        // Переходящий остаток - bu davr boshida aktiv bo'lgan bemorlar
        $carryOverQuery = Accommodation::query()
            ->whereIn('medical_history_id', $medicalHistoryIds)
            ->where('admission_date', '<', $this->startDate)
            ->where(function ($q)  {
                $q->where('discharge_date', '>', $this->startDate)
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
            ->where('payments.created_at', '<', $this->startDate)
            ->selectRaw('SUM(sessions * price) as total')
            ->value('total') ?? 0;

        // Procedure payments
        $carryOverAmount += ProcedurePaymentDetail::query()
            ->join('procedure_payments', 'procedure_payment_details.procedure_payment_id', '=', 'procedure_payments.id')
            ->join('payments', 'procedure_payments.payment_id', '=', 'payments.id')
            ->whereIn('payments.medical_history_id', $carryOverHistoryIds)
            ->where('payments.created_at', '<', $this->startDate)
            ->selectRaw('SUM(sessions * price) as total')
            ->value('total') ?? 0;

        // Accommodation payments
        $carryOverAmount += AccommodationPayment::query()
            ->join('payments', 'accommodation_payments.payment_id', '=', 'payments.id')
            ->whereIn('payments.medical_history_id', $carryOverHistoryIds)
            ->where('payments.created_at', '<', $this->startDate)
            ->selectRaw('SUM((tariff_price * COALESCE(ward_day, 0)) + (meal_price * COALESCE(meal_day, 0))) as total')
            ->value('total') ?? 0;

        return $carryOverAmount;
    }

    public function getEmptyStats(): array
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

    public function formatCurrency(float $amount): string
    {
        return number_format($amount, 0, '.', ' ') . ' сум';
    }
    protected function getFormSchema(): array
    {
        return [
            Group::make()
                ->schema([
                    DatePicker::make('startDate')
                        ->label('Начальная дата')
                        ->default(now()->startOfMonth())
                        ->required(),
                    DatePicker::make('endDate')
                        ->label('Конечная дата')
                        ->default(now())
                        ->required(),
                ])
                ->columns(3),
        ];
    }



    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }


    public function getTitle(): string
    {
        return 'Стационарные платежи';
    }
}

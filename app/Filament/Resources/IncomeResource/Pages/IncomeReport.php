<?php

namespace App\Filament\Resources\IncomeResource\Pages;

use App\Filament\Resources\IncomeResource;
use Filament\Actions;
use Filament\Forms\Components\Group;
use Filament\Resources\Pages\page;
use App\Models\Payment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Form;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IncomeReport extends page
{
    protected static string $resource = IncomeResource::class;
    protected static ?string $title = 'Отчет по доходам';

    protected static string $view = 'filament.resources.payment-resource.income-report';

    public $startDate;
    public $endDate;

    public $chartData = [];
    public $totalIncome = 0;
    public $nak = 0;
    public $terminal = 0;
    public $transfer = 0;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
        $this->generateReport();
    }

    public function updated($property)
    {
        $this->generateReport();
    }

    public function generateReport(): void
    {
        $payments = Payment::with('paymentType')
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ])
            ->get();

        $this->chartData = [];
        $this->totalIncome = 0;
        $this->nak = 0;
        $this->terminal = 0;
        $this->transfer = 0;

        foreach ($payments as $payment) {
            $amount = $payment->getTotalPaidAmount(); // <-- sizning metod

            $dateKey = $payment->created_at->format('Y-m-d');
            if (!isset($this->chartData[$dateKey])) {
                $this->chartData[$dateKey] = 0;
            }
            $this->chartData[$dateKey] += $amount;

            $this->totalIncome += $amount;

            match ($payment->payment_type_id) {
                1 => $this->nak += $amount,
                2 => $this->terminal += $amount,
                3 => $this->transfer += $amount,
                default => null
            };
        }

        ksort($this->chartData);
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
                ->columns(2),
        ];
    }
}

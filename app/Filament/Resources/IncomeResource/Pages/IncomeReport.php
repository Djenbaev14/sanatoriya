<?php

namespace App\Filament\Resources\IncomeResource\Pages;

use App\Filament\Resources\IncomeResource;
use App\Models\AccommodationPayment;
use App\Models\LabTestPaymentDetail;
use App\Models\Procedure;
use App\Models\ProcedurePaymentDetail;
use Filament\Actions;
use Filament\Forms\Components\Group;
use Filament\Resources\Pages\Page;
use App\Models\Payment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Form;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IncomeReport extends Page
{
    protected static string $resource = IncomeResource::class;
    protected static ?string $title = 'Статистика по услугам';
    protected static string $view = 'filament.resources.payment-resource.income-report';

    public $startDate;
    public $endDate;
    public $selectedProcedure;

    public $chartData = [];
    public $chartData2 = [];
    public $chartDataAmount = [];
    public $chartDataIncome = [];
    public $tableData = [];
    public $tableData2 = [];
    public $totalIncome = 0;
    public $totalSessions = 0;
    public $totalIncome2 = 0;
    public $totalSessions2 = 0;
    public $nak = 0;
    public $terminal = 0;
    public $transfer = 0;
    public $procedureAmount = 0;
    public $labTestAmount = 0;
    public $koykaAmount = 0;
    public $pitanieAmount = 0;
    public $koykaUxodAmount = 0;
    public $pitanieUxodAmount = 0;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
        $this->generateReport();
        $this->getPayments();
    }
    public function updated($property)
    {
        $this->generateReport();
        $this->getPayments();
    }
    public function getPayments(): void
    {
        $labTests=LabTestPaymentDetail::query()
            ->join('lab_test_payments', 'lab_test_payment_details.lab_test_payment_id', '=', 'lab_test_payments.id')
            ->join('payments', 'lab_test_payments.payment_id', '=', 'payments.id')->whereBetween('payments.created_at', [$this->startDate, $this->endDate]);
        
        $procedures=ProcedurePaymentDetail::query()
            ->join('procedure_payments', 'procedure_payment_details.procedure_payment_id', '=', 'procedure_payments.id')
            ->join('payments', 'procedure_payments.payment_id', '=', 'payments.id')->whereBetween('payments.created_at', [$this->startDate, $this->endDate]);
        
        $accommodation=AccommodationPayment::query()
            ->join('payments', 'accommodation_payments.payment_id', '=', 'payments.id')
            ->whereNotNull('accommodation_payments.medical_history_id')->whereBetween('payments.created_at', [$this->startDate, $this->endDate]);
        $accommodationPartner=AccommodationPayment::query()
            ->join('payments', 'accommodation_payments.payment_id', '=', 'payments.id')
            ->whereNull('accommodation_payments.medical_history_id')->whereBetween('payments.created_at', [$this->startDate, $this->endDate]);
        
        $this->labTestAmount=$labTests->selectRaw('SUM(sessions * price) as total')
            ->value('total') ?? 0;
        $this->procedureAmount = $procedures->selectRaw('SUM(sessions * price) as total')
            ->value('total') ?? 0;
        $this->koykaAmount = $accommodation->selectRaw('SUM(tariff_price * COALESCE(ward_day, 0)) as total')->value('total') ?? 0;
        $this->pitanieAmount = $accommodation->selectRaw('SUM(meal_price * COALESCE(meal_day, 0)) as total')->value('total') ?? 0;
        $this->koykaUxodAmount = $accommodationPartner->selectRaw('SUM(tariff_price * COALESCE(ward_day, 0)) as total')->value('total') ?? 0;
        $this->pitanieUxodAmount = $accommodationPartner->selectRaw('SUM(meal_price * COALESCE(meal_day, 0)) as total')->value('total') ?? 0;
        
        $this->chartDataAmount = [
            [
                'name' => 'Анализы',
                'amount' => $this->labTestAmount,
            ],
            [
                'name' => 'Процедуры',
                'amount' => $this->procedureAmount,
            ],
            [
                'name' => 'Койка',
                'amount' => $this->koykaAmount,
            ],
            [
                'name' => 'Питание',
                'amount' => $this->pitanieAmount,
            ],
            [
                'name' => 'Койка (Уход)',
                'amount' => $this->koykaUxodAmount,
            ],
            [
                'name' => 'Питание (Уход)',
                'amount' => $this->pitanieUxodAmount,
            ]
        ];
    }
    public function generateReport(): void
    {
        // Get payment statistics by payment type
        $payments = Payment::with('paymentType')
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ])
            ->get();

        $this->nak = 0;
        $this->terminal = 0;
        $this->transfer = 0;

        foreach ($payments as $payment) {
            $amount = $payment->getTotalPaidAmount(); // <-- sizning metod

            $dateKey = $payment->created_at->format('Y-m-d');
            if (!isset($this->chartDataIncome[$dateKey])) {
                $this->chartDataIncome[$dateKey] = 0;
            }
            $this->chartDataIncome[$dateKey] += $amount;

            match ($payment->payment_type_id) {
                1 => $this->nak += $amount,
                2 => $this->terminal += $amount,
                3 => $this->transfer += $amount,
                default => null
            };
        }

        ksort($this->chartDataIncome);
        

        // Get procedure statistics
        $labTestStats = DB::table('lab_test_payment_details as ppd')
            ->join('lab_tests as p', 'ppd.lab_test_id', '=', 'p.id')
            ->join('lab_test_payments as pp', 'ppd.lab_test_payment_id', '=', 'pp.id')
            ->join('payments as pay', 'pp.payment_id', '=', 'pay.id')
            ->whereBetween('pay.created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ])
            ->whereNull('ppd.deleted_at')
            ->select(
                'p.name as lab_test_name',
                DB::raw('SUM(ppd.sessions) as total_sessions'),
                DB::raw('SUM(ppd.price * ppd.sessions) as total_amount')
            )
            ->groupBy('p.id', 'p.name')
            ->orderBy('total_amount', 'desc')
            ->get();

        // Prepare chart data
        $this->chartData2 = [];
        $this->tableData2 = [];
        $this->totalSessions2 = 0;

        foreach ($labTestStats as $stat) {
            $this->chartData2[] = [
                'name' => $stat->lab_test_name,
                'sessions' => $stat->total_sessions,
                'amount' => $stat->total_amount
            ];
            // Add to table data
            $this->tableData2[] = [
                'service' => $stat->lab_test_name,
                'count' => $stat->total_sessions,
                'amount' => $stat->total_amount
            ];

            $this->totalIncome2 += $stat->total_amount;
            $this->totalSessions2 += $stat->total_sessions;
        }
        usort($this->tableData2, function ($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });

        // Get procedure statistics
        $procedureStats = DB::table('procedure_payment_details as ppd')
            ->join('procedures as p', 'ppd.procedure_id', '=', 'p.id')
            ->join('procedure_payments as pp', 'ppd.procedure_payment_id', '=', 'pp.id')
            ->join('payments as pay', 'pp.payment_id', '=', 'pay.id')
            ->whereBetween('pay.created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ])
            ->whereNull('ppd.deleted_at')
            ->select(
                'p.name as procedure_name',
                DB::raw('SUM(ppd.sessions) as total_sessions'),
                DB::raw('SUM(ppd.price * ppd.sessions) as total_amount')
            )
            ->groupBy('p.id', 'p.name')
            ->orderBy('total_amount', 'desc')
            ->get();

        // Prepare chart data
        $this->chartData = [];
        $this->tableData = [];
        $this->totalSessions = 0;

        foreach ($procedureStats as $stat) {
            $this->chartData[] = [
                'name' => $stat->procedure_name,
                'sessions' => $stat->total_sessions,
                'amount' => $stat->total_amount
            ];
            // Add to table data
            $this->tableData[] = [
                'service' => $stat->procedure_name,
                'count' => $stat->total_sessions,
                'amount' => $stat->total_amount
            ];

            $this->totalIncome += $stat->total_amount;
            $this->totalSessions += $stat->total_sessions;
        }
        usort($this->tableData, function ($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });
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

    public function exportExcel()
    {
        // Add Excel export logic here
        session()->flash('success', 'Excel экспорт функционал в разработке');
    }

    public function exportPdf()
    {
        // Add PDF export logic here
        session()->flash('success', 'PDF экспорт функционал в разработке');
    }

    public function printReport()
    {
        // Add print logic here
        $this->dispatchBrowserEvent('print-report');
    }
}
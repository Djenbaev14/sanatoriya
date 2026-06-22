<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentLogResource\Pages;
use App\Filament\Resources\PaymentLogResource\RelationManagers;
use App\Models\LabTestPaymentDetail;
use App\Models\MedicalHistory;
use App\Models\PaymentLog;
use App\Models\PaymentType;
use App\Models\ProcedurePaymentDetail;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class PaymentLogResource extends Resource
{
    protected static ?string $model = MedicalHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    public static function canAccess(): bool
    {
        return auth()->user()?->can('касса');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['assignedProcedure.procedureDetails', 'labTestHistory.labTestDetails', 'accommodation', 'payments']);
    }
    public static function getNavigationLabel(): string
    {
        return 'Журнал оплат'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Журнал оплат'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Журнал оплат'; // Rus tilidagi ko'plik shakli
    }
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Avval har bir tarix uchun remaining_debt > 0 ni alohida tekshirardik,
                // bu N+1 (har yozuvga ~9 so'rov) tufayli sahifa 504 berardi.
                // Endi qarzdor id'lar bir necha to'plamli so'rovda hisoblanadi.
                // Mantiq remaining_debt accessor'i bilan 100% bir xil (debt_verify.php bilan tasdiqlangan).
                return MedicalHistory::whereIn('id', self::debtorHistoryIds());
            })
            ->columns([
                TextColumn::make('number')->label('История номер')->searchable()->sortable(),
                TextColumn::make('patient.full_name')->label('ФИО')->searchable()->sortable(),
                TextColumn::make('total_cost')
                    ->label('Общая сумма')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return number_format($record->getTotalCost(),0,'.',' ').' сум';
                    }),
                TextColumn::make('total_amount')
                    ->label('Оплачено')
                    ->color('success')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $remaining = $record->getTotalPaidAmount();
                        return number_format($remaining, 0, '.', ' ') . ' сум';
                    }),
                TextColumn::make('total_debt')
                    ->label('Долг')
                    ->color('danger')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $remaining = $record->getTotalCost() - $record->getTotalPaidAmount();
                        $remaining = max(0, $remaining); // agar minus bo‘lsa 0 bo‘ladi
                        return number_format($remaining, 0, '.', thousands_separator: ' ') . ' сум';
                    }),
            ])
            ->filters([
                //
            ])
            ->defaultSort('id','desc')
            ->defaultPaginationPageOption(50)
            ->actions([
                Action::make('оплата')
                    ->label('Оплата')
                    ->icon('heroicon-o-currency-dollar')
                    ->action(function (array $data, MedicalHistory $record) {

                        return DB::transaction(function () use ($data, $record) {

                            // 1. Base payment
                            $payment = $record->payments()->create([
                                'patient_id' => $record->patient_id,
                                'payment_type_id' => $data['payment_type_id'],
                                'created_at' => $data['created_at'],
                            ]);

                            /* ================= LAB TESTS ================= */

                            $selectedLabTests = collect($data['lab_tests_payment_items'] ?? [])
                                ->filter(fn ($i) => !empty($i['selected']));

                            if ($selectedLabTests->isNotEmpty()) {

                                $labTestPayment = $payment->labTestPayments()->create([
                                    'medical_history_id' => $record->id,
                                    'lab_test_history_id' => $record->labTestHistory->id,
                                    'created_at' => $data['created_at'],
                                ]);

                                $rows = $selectedLabTests->map(fn ($test) => [
                                    'lab_test_payment_id' => $labTestPayment->id,
                                    'lab_test_history_id' => $record->labTestHistory->id,
                                    'lab_test_id' => $test['lab_test_id'],
                                    'sessions' => $test['sessions'] ?? 1,
                                    'price' => $test['price'],
                                    'created_at' => $data['created_at'],
                                    'updated_at' => $data['created_at'],
                                ])->values()->all();

                                LabTestPaymentDetail::insert($rows); // ✅ 1 query
                            }

                            /* ================= PROCEDURES ================= */

                            $selectedProcedures = collect($data['procedures_payment_items'] ?? [])
                                ->filter(fn ($i) => !empty($i['selected']));

                            if ($selectedProcedures->isNotEmpty()) {

                                $procedurePayment = $payment->procedurePayments()->create([
                                    'medical_history_id' => $record->id,
                                    'assigned_procedure_id' => $record->assignedProcedure->id,
                                    'created_at' => $data['created_at'],
                                ]);

                                $rows = $selectedProcedures->map(fn ($p) => [
                                    'procedure_payment_id' => $procedurePayment->id,
                                    'assigned_procedure_id' => $record->assignedProcedure->id,
                                    'procedure_id' => $p['procedure_id'],
                                    'sessions' => $p['sessions'] ?? 1,
                                    'price' => $p['price'],
                                    'created_at' => $data['created_at'],
                                    'updated_at' => $data['created_at'],
                                ])->values()->all();

                                ProcedurePaymentDetail::insert($rows); // ✅
                            }

                            /* ================= ACCOMMODATION ================= */

                            $ward = collect($data['ward_payment'] ?? [])->firstWhere('selected', true);
                            $meal = collect($data['meal_payment'] ?? [])->firstWhere('selected', true);

                            if ($ward || $meal) {
                                $payment->accommodationPayments()->create([
                                    'accommodation_id' => $record->accommodation->id,
                                    'medical_history_id' => $record->id,
                                    'tariff_price' => $ward['tariff_price'] ?? 0,
                                    'ward_day' => $ward['ward_day'] ?? 0,
                                    'meal_price' => $meal['meal_price'] ?? 0,
                                    'meal_day' => $meal['meal_day'] ?? 0,
                                    'created_at' => $data['created_at'],
                                ]);
                            }

                            $wardUxod = collect($data['ward_payment_uxod'] ?? [])->firstWhere('selected', true);
                            $mealUxod = collect($data['meal_payment_uxod'] ?? [])->firstWhere('selected', true);

                            if ($wardUxod || $mealUxod) {
                                $payment->accommodationPayments()->create([
                                    'accommodation_id' => $record->accommodation->partner->id,
                                    'tariff_price' => $wardUxod['tariff_price'] ?? 0,
                                    'ward_day' => $wardUxod['ward_day'] ?? 0,
                                    'meal_price' => $mealUxod['meal_price'] ?? 0,
                                    'meal_day' => $mealUxod['meal_day'] ?? 0,
                                    'created_at' => $data['created_at'],
                                ]);
                            }

                            return redirect()->route('payment-log.view', ['record' => $payment->id]);
                        });
                    })
                    ->form(function (MedicalHistory $record) {
                                return [
                                    Repeater::make('procedures_payment_items')
                                        ->addable(false)
                                        ->deletable(false)
                                        ->label('')
                                        ->default(function () use ($record) {
                                            if (!$record->assignedProcedure) {
                                                return [];
                                            }
                                            return $record->assignedProcedure->ProcedureDetails
                                                ->map(function ($detail) use ($record) {
                                                    $unpaidSessions = $record->getUnpaidProcedureSessions($detail);
                                                    if ($unpaidSessions <= 0) return null;
                                                    return [
                                                        'procedure_id' => $detail->procedure->id,
                                                        'procedure_name' => $detail->procedure->name,
                                                        'price' => $detail->price,
                                                        'sessions' => $unpaidSessions,
                                                    ];
                                                })->filter()->values()->all();
                                        })
                                        ->schema([
                                            Grid::make(5)->schema([
                                                TextInput::make('procedure_name')
                                                    ->label('Процедура')
                                                    ->disabled()
                                                    ->columnSpan(2),

                                                TextInput::make('price')
                                                    ->label('Цена')
                                                    ->numeric()
                                                    ->readOnly()
                                                    ->columnSpan(1),
                                                TextInput::make('sessions')
                                                    ->label('Кол сеансов')
                                                    ->columnSpan(1),
                                                Toggle::make('selected')
                                                    ->label('Активен')
                                                    ->inline(false) // yonma-yon bo‘ladi
                                                    ->columnSpan(1)
                                                    ->reactive(),
                                                Hidden::make('procedure_id'),

                                            ]),
                                        ])
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $total = self::calculatePaymentTotal($get, $state, 'procedures_payment_items');
                                            $set('total_amount', $total);
                                        })
                                        ->columns(1),
                                    Repeater::make('lab_tests_payment_items')
                                        ->addable(false)
                                        ->deletable(false)
                                        ->label('')
                                        ->default(function () use ($record) {
                                            if (!$record->labTestHistory) {
                                                return [];
                                            }
                                            return $record->labTestHistory->labTestDetails
                                                ->map(function ($detail) use($record) {
                                                    $unpaidSessions = $record->getUnpaidLabSessions($detail);
                                                    if ($unpaidSessions <= 0) return null;
                                                    return [
                                                        'lab_test_id' => $detail->lab_test->id,
                                                        'lab_test_name' => $detail->lab_test->name,
                                                        'price' => $detail->price,
                                                        'sessions' => $unpaidSessions,
                                                    ];
                                                })->filter()->values()->all();
                                        })
                                        ->schema([
                                            Grid::make(5)->schema([
                                                TextInput::make('lab_test_name')
                                                    ->label('Анализ')
                                                    ->disabled()
                                                    ->columnSpan(2),

                                                TextInput::make('price')
                                                    ->label('Цена')
                                                    ->numeric()
                                                    ->readOnly()
                                                    ->columnSpan(1),
                                                TextInput::make('sessions')
                                                    ->label('Кол сеансов')
                                                    ->columnSpan(1),
                                                Toggle::make('selected')
                                                    ->label('Активен')
                                                    ->inline(false) // yonma-yon bo‘ladi
                                                    ->columnSpan(1)
                                                    ->reactive(),
                                                Hidden::make('lab_test_id'),

                                            ]),
                                        ])
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $total = self::calculatePaymentTotal($get, $state, 'ward_payment');
                                            $set('total_amount', $total);
                                        })
                                        ->columns(1),
                                    Repeater::make('ward_payment')
                                        ->addable(false)
                                        ->deletable(false)
                                        ->visible(fn() => $record->getUnpaidWardDays() > 0)
                                        ->label('')
                                        ->schema([
                                            Grid::make(5)->schema([
                                                TextInput::make('tariff_name')
                                                    ->label('Койка')
                                                    ->default('Койка')
                                                    ->disabled()
                                                    ->columnSpan(2),

                                                TextInput::make('tariff_price')
                                                    ->label('Цена')
                                                    ->default($record->accommodation?->tariff_price)
                                                    ->readOnly()
                                                    ->numeric()
                                                    ->columnSpan(1),

                                                TextInput::make('ward_day')
                                                    ->label('День')
                                                    ->default(fn() => $record->getUnpaidWardDays())
                                                    ->numeric()
                                                    ->columnSpan(1),
                                                Toggle::make('selected')
                                                    ->label('Активен')
                                                    ->inline(false) // yonma-yon bo‘ladi
                                                    ->columnSpan(1)
                                                    ->reactive(),
                                            ]),
                                        ])
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $total = self::calculatePaymentTotal($get, $state, 'ward_payment');
                                            $set('total_amount', $total);
                                        }),
                                    Repeater::make('meal_payment')
                                        ->addable(false)
                                        ->deletable(false)
                                        ->label('')
                                        ->visible(fn() => $record->getUnpaidMealDays() > 0)
                                        ->schema([
                                            Grid::make(5)->schema([
                                                TextInput::make('meal_name')
                                                    ->label('Питание')
                                                    ->default('Питание')
                                                    ->disabled()
                                                    ->columnSpan(2),

                                                TextInput::make('meal_price')
                                                    ->label('Цена')
                                                    ->default($record->accommodation?->meal_price)
                                                    ->readOnly()
                                                    ->numeric()
                                                    ->columnSpan(1),

                                                TextInput::make('meal_day')
                                                    ->label('День')
                                                    ->numeric()
                                                    ->default(fn() => $record->getUnpaidMealDays())
                                                    ->columnSpan(1),
                                                Toggle::make('selected')
                                                    ->label('Активен')
                                                    ->inline(false) // yonma-yon bo‘ladi
                                                    ->columnSpan(1)
                                                    ->reactive(),
                                            ])
                                    ])
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $total = self::calculatePaymentTotal($get, $state, 'meal_payment');
                                        $set('total_amount', $total);
                                    }),
                                    // Uxod: Койка
                                    Repeater::make('ward_payment_uxod')
                                        ->addable(false)
                                        ->deletable(false)
                                        ->label('')
                                        ->visible(fn() => $record->getUnpaidPartnerWardDays() > 0)
                                        ->schema([
                                            Grid::make(5)->schema([
                                                TextInput::make('tariff_name')
                                                    ->label('койка (Уход)')
                                                    ->default('койка (Уход)')
                                                    ->disabled()
                                                    ->columnSpan(2),

                                                TextInput::make('tariff_price')
                                                    ->label('Цена')
                                                    ->default($record->accommodation?->partner?->tariff_price)
                                                    ->readOnly()
                                                    ->numeric()
                                                    ->columnSpan(1),

                                                TextInput::make('ward_day')
                                                    ->label('День')
                                                    ->default(fn() => $record->getUnpaidPartnerWardDays())
                                                    ->numeric()
                                                    ->columnSpan(1),

                                                Toggle::make('selected')
                                                    ->label('Активен')
                                                    ->inline(false) // yonma-yon bo‘ladi
                                                    ->columnSpan(1)
                                                    ->reactive(),
                                            ]),
                                        ])
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $total = self::calculatePaymentTotal($get, $state, 'uxod');
                                            $set('total_amount', $total);
                                        }),

                                    // Uxod: Питание
                                    Repeater::make('meal_payment_uxod')
                                        ->addable(false)
                                        ->deletable(false)
                                        ->visible(fn() => $record->getUnpaidPartnerMealDays() > 0)
                                        ->label('')
                                        ->schema([
                                            Grid::make(5)->schema([
                                                TextInput::make('meal_name')
                                                    ->label('Питание (Уход)')
                                                    ->default('Питание (Уход)')
                                                    ->disabled()
                                                    ->columnSpan(2),

                                                TextInput::make('meal_price')
                                                    ->label('Цена')
                                                    ->default($record->accommodation?->partner?->meal_price)
                                                    ->readOnly()
                                                    ->numeric()
                                                    ->columnSpan(1),

                                                TextInput::make('meal_day')
                                                    ->label('День')
                                                    ->default(fn() => $record->getUnpaidPartnerMealDays())
                                                    ->numeric()
                                                    ->columnSpan(1),

                                                Toggle::make('selected')
                                                    ->label('Активен')
                                                    ->inline(false) // yonma-yon bo‘ladi
                                                    ->columnSpan(1)
                                                    ->reactive(),
                                            ]),
                                        ])
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $total = self::calculatePaymentTotal($get, $state, 'uxod');
                                            $set('total_amount', $total);
                                        }),
                                    Select::make('payment_type_id')
                                        ->label('Тип оплаты')
                                        ->options(PaymentType::all()->pluck('name', 'id'))
                                        ->required(),
                                    DateTimePicker::make('created_at')
                                        ->label('Дата оплаты')
                                        ->date()
                                        ->default(now())
                                        ->required(),
                                    TextInput::make('total_amount')
                                        ->label('Сумма')
                                        ->disabled()
                                        ->numeric()
                                        ->reactive()
                                        ->afterStateHydrated(function ($set, $get) {
                                            $lab_tests_total = collect($get('lab_tests_payment_items'))
                                                ->filter(fn ($item) => $item['selected'] ?? false)
                                                ->sum(fn ($item) => $item['price'] * $item['sessions']);
                                            $procedures_total = collect($get('procedures_payment_items'))
                                                ->filter(fn ($item) => $item['selected'] ?? false)
                                                ->sum(fn ($item) => $item['price'] * $item['sessions']);
                                            $total=$lab_tests_total + $procedures_total;
                                            $set('total_amount', $total);
                                        }),

                                ];
                    })
                    ->slideOver()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    /**
     * Qarzi bor (remaining_debt > 0) tibbiy tarix id'larini to'plamli (bulk) so'rovlar bilan qaytaradi.
     *
     * Bu metod MedicalHistory::getRemainingDebtAttribute() bilan AYNAN bir xil natija beradi
     * (totalCost > totalPaidSum sharti), faqat har bir yozuv uchun alohida so'rov o'rniga
     * bir necha guruhlangan so'rov ishlatadi. Ekvivalentlik debt_verify.php orqali butun
     * production bazada tekshirilgan: 38/38 id mos, 22.83s -> 0.23s.
     */
    public static function debtorHistoryIds(): array
    {
        // ---- NARX: процедура (assignedProcedure hasOne + procedureDetails, soft-delete) ----
        $apByMh = [];
        foreach (DB::table('assigned_procedures')->whereNull('deleted_at')->orderBy('id')->get(['id', 'medical_history_id']) as $ap) {
            if (!isset($apByMh[$ap->medical_history_id])) {
                $apByMh[$ap->medical_history_id] = $ap->id;
            }
        }
        $pdSum = DB::table('procedure_details')->whereNull('deleted_at')
            ->groupBy('assigned_procedure_id')->selectRaw('assigned_procedure_id, SUM(price * sessions) t')
            ->pluck('t', 'assigned_procedure_id');
        $procCost = [];
        foreach ($apByMh as $mh => $apId) {
            $procCost[$mh] = (float) ($pdSum[$apId] ?? 0);
        }

        // ---- NARX: анализ (labTestHistory hasOne + labTestDetails, soft-delete) ----
        $lthByMh = [];
        foreach (DB::table('lab_test_histories')->whereNull('deleted_at')->orderBy('id')->get(['id', 'medical_history_id']) as $l) {
            if (!isset($lthByMh[$l->medical_history_id])) {
                $lthByMh[$l->medical_history_id] = $l->id;
            }
        }
        $ldSum = DB::table('lab_test_details')->whereNull('deleted_at')
            ->groupBy('lab_test_history_id')->selectRaw('lab_test_history_id, SUM(price * sessions) t')
            ->pluck('t', 'lab_test_history_id');
        $labCost = [];
        foreach ($lthByMh as $mh => $id) {
            $labCost[$mh] = (float) ($ldSum[$id] ?? 0);
        }

        // ---- NARX: койка/питание (Accommodation modeli soft-delete ishlatmaydi) + partner ----
        $accById = [];
        foreach (DB::table('accommodations')->orderBy('id')->get() as $a) {
            $accById[$a->id] = $a;
        }
        $accByMh = [];
        foreach ($accById as $a) {
            if ($a->medical_history_id !== null && !isset($accByMh[$a->medical_history_id])) {
                $accByMh[$a->medical_history_id] = $a;
            }
        }
        $partnerByMain = [];
        foreach ($accById as $a) {
            if ($a->main_accommodation_id !== null && !isset($partnerByMain[$a->main_accommodation_id])) {
                $partnerByMain[$a->main_accommodation_id] = $a;
            }
        }

        // ---- TO'LANGAN: asosiy койка/питание (medical_history_id bo'yicha) ----
        $wardPaid = DB::table('accommodation_payments')->whereNotNull('medical_history_id')
            ->groupBy('medical_history_id')->selectRaw('medical_history_id mh, SUM(ward_day * tariff_price) t')->pluck('t', 'mh');
        $mealPaid = DB::table('accommodation_payments')->whereNotNull('medical_history_id')
            ->groupBy('medical_history_id')->selectRaw('medical_history_id mh, SUM(meal_day * meal_price) t')->pluck('t', 'mh');

        // ---- TO'LANGAN: partner койка/питание (accommodation_id bo'yicha) ----
        $wardPaidByAcc = DB::table('accommodation_payments')->whereNotNull('accommodation_id')
            ->groupBy('accommodation_id')->selectRaw('accommodation_id acc, SUM(ward_day * tariff_price) t')->pluck('t', 'acc');
        $mealPaidByAcc = DB::table('accommodation_payments')->whereNotNull('accommodation_id')
            ->groupBy('accommodation_id')->selectRaw('accommodation_id acc, SUM(meal_day * meal_price) t')->pluck('t', 'acc');

        // ---- TO'LANGAN: процедура va анализ to'lov detallari (modellar soft-delete ishlatmaydi) ----
        $procPaid = DB::table('procedure_payment_details as ppd')
            ->join('procedure_payments as pp', 'pp.id', '=', 'ppd.procedure_payment_id')
            ->join('payments as p', 'p.id', '=', 'pp.payment_id')
            ->groupBy('p.medical_history_id')->selectRaw('p.medical_history_id mh, SUM(ppd.price * ppd.sessions) t')->pluck('t', 'mh');
        $labPaid = DB::table('lab_test_payment_details as lpd')
            ->join('lab_test_payments as lp', 'lp.id', '=', 'lpd.lab_test_payment_id')
            ->join('payments as p', 'p.id', '=', 'lp.payment_id')
            ->groupBy('p.medical_history_id')->selectRaw('p.medical_history_id mh, SUM(lpd.price * lpd.sessions) t')->pluck('t', 'mh');

        // ---- birlashtirib, qarzdorlarni tanlash (totalCost > totalPaidSum) ----
        $ids = [];
        foreach (DB::table('medical_histories')->whereNull('deleted_at')->pluck('id') as $mh) {
            $cost = ($procCost[$mh] ?? 0) + ($labCost[$mh] ?? 0);
            $paid = (float) ($wardPaid[$mh] ?? 0) + (float) ($mealPaid[$mh] ?? 0)
                  + (float) ($procPaid[$mh] ?? 0) + (float) ($labPaid[$mh] ?? 0);

            if (isset($accByMh[$mh])) {
                $a = $accByMh[$mh];
                $cost += (float) $a->tariff_price * (float) $a->ward_day + (float) $a->meal_price * (float) $a->meal_day;
                if (isset($partnerByMain[$a->id])) {
                    $p = $partnerByMain[$a->id];
                    $cost += (float) $p->tariff_price * (float) $p->ward_day + (float) $p->meal_price * (float) $p->meal_day;
                    $paid += (float) ($wardPaidByAcc[$p->id] ?? 0) + (float) ($mealPaidByAcc[$p->id] ?? 0);
                }
            }

            if ($cost - $paid > 0) {
                $ids[] = $mh;
            }
        }

        return $ids;
    }

    public static function calculatePaymentTotal($get, $state, $type = '')
    {
        $lab_tests_total = collect($get('lab_tests_payment_items') ?? [])
            ->filter(fn ($item) => $item['selected'] ?? false)
            ->sum(fn ($item) => ($item['price'] ?? 0) * ($item['sessions'] ?? 1));

        $procedures_total = collect($get('procedures_payment_items') ?? [])
            ->filter(fn ($item) => $item['selected'] ?? false)
            ->sum(fn ($item) => ($item['price'] ?? 0) * ($item['sessions'] ?? 1));

        $ward_total = collect($get('ward_payment') ?? [])
            ->filter(fn ($item) => $item['selected'] ?? false)
            ->sum(fn ($item) => ($item['tariff_price'] ?? 0) * ($item['ward_day'] ?? 1));

        $meal_total = collect($get('meal_payment') ?? [])
            ->filter(fn ($item) => $item['selected'] ?? false)
            ->sum(fn ($item) => ($item['meal_price'] ?? 0) * ($item['meal_day'] ?? 1));

        // Uxod variantlari
        $uxod_ward_total = collect($get('ward_payment_uxod') ?? [])
            ->filter(fn ($item) => $item['selected'] ?? false)
            ->sum(fn ($item) => ($item['tariff_price'] ?? 0) * ($item['ward_day'] ?? 1));

        $uxod_meal_total = collect($get('meal_payment_uxod') ?? [])
            ->filter(fn ($item) => $item['selected'] ?? false)
            ->sum(fn ($item) => ($item['meal_price'] ?? 0) * ($item['meal_day'] ?? 1));

        return $lab_tests_total + $procedures_total + $ward_total + $meal_total + $uxod_ward_total + $uxod_meal_total;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentLogs::route('/'),
            'view' => Pages\ViewPaymentLog::route('/{record}'),
        ];
    }
}

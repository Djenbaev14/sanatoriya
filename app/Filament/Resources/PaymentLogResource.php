<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentLogResource\Pages;
use App\Filament\Resources\PaymentLogResource\RelationManagers;
use App\Models\MedicalHistory;
use App\Models\PaymentLog;
use App\Models\PaymentType;
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
                $all = $query->get();

                $ids = $all->filter(function ($history) {
                    return $history->getRemainingDebt() > 0;
                })->pluck('id');

                return MedicalHistory::whereIn('id', $ids);
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
                        try {
                            // 1. Create base payment
                            $payment = $record->payments()->create([
                                'patient_id' => $record->patient_id,
                                'payment_type_id' => $data['payment_type_id'],
                                'created_at' => $data['created_at'],
                            ]);

                            // 2. Create lab_test_payments & lab_test_payment_details
                            $labTestPayment = null;
                            $selectedLabTests = collect($data['lab_tests_payment_items'] ?? [])
                                ->filter(fn($item) => $item['selected'] ?? false);

                            if ($selectedLabTests->isNotEmpty()) {
                                $labTestPayment = $payment->labTestPayments()->create([
                                    'medical_history_id' => $record->id,
                                    'lab_test_history_id' => $record->labTestHistory->id,
                                    'created_at' => $data['created_at'],
                                ]);

                                foreach ($selectedLabTests as $test) {
                                    $labTestPayment->labTestPaymentDetails()->create([
                                        'lab_test_history_id' => $record->labTestHistory->id,
                                        'lab_test_id' => $test['lab_test_id'],
                                        'sessions' => $test['sessions'] ?? 1,
                                        'price' => $test['price'],
                                        'created_at' => $data['created_at'],
                                    ]);
                                }
                            }

                            // 3. Create procedure_payments & procedure_payment_details
                            $procedurePayment = null;
                            $selectedProcedures = collect($data['procedures_payment_items'] ?? [])
                                ->filter(fn($item) => $item['selected'] ?? false);

                            if ($selectedProcedures->isNotEmpty()) {
                                $procedurePayment = $payment->procedurePayments()->create([
                                    'medical_history_id' => $record->id,
                                    'assigned_procedure_id' => $record->assignedProcedure->id,
                                    'created_at' => $data['created_at'],
                                ]);

                                foreach ($selectedProcedures as $procedure) {
                                    $procedurePayment->procedurePaymentDetails()->create([
                                        'assigned_procedure_id' => $record->assignedProcedure->id,
                                        'procedure_id' => $procedure['procedure_id'],
                                        'sessions' => $procedure['sessions'] ?? 1,
                                        'price' => $procedure['price'],
                                        'created_at' => $data['created_at'],
                                    ]);
                                }
                            }

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

                            
                            $ward_uxod = collect($data['ward_payment_uxod'] ?? [])->firstWhere('selected', true);
                            $meal_uxod = collect($data['meal_payment_uxod'] ?? [])->firstWhere('selected', true);

                            if ($ward_uxod || $meal_uxod) {
                                $payment->accommodationPayments()->create([
                                    'accommodation_id' => $record->accommodation->partner->id,
                                    'tariff_price' => $ward_uxod['tariff_price'] ?? 0,
                                    'ward_day' => $ward_uxod['ward_day'] ?? 0,
                                    'meal_price' => $meal_uxod['meal_price'] ?? 0,
                                    'meal_day' => $meal_uxod['meal_day'] ?? 0,
                                    'created_at' => $data['created_at'],
                                ]);
                            }

                            return redirect()->route('payment-log.view', ['record' => $payment->id]);

                        } catch (\Throwable $e) {
                            throw new \Exception("Ошибка при оплате: " . $e->getMessage());
                        }
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

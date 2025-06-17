<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignedProcedureResource\Pages;
use App\Filament\Resources\AssignedProcedureResource\RelationManagers;
use App\Models\AssignedProcedure;
use App\Models\Bed;
use App\Models\MealType;
use App\Models\PaymentType;
use App\Models\Procedure;
use App\Models\Tariff;
use App\Models\Ward;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssignedProcedureResource extends Resource
{
    protected static ?string $model = AssignedProcedure::class;

    protected static ?string $navigationGroup = 'Касса';
    protected static ?int $navigationSort = 1;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status_payment_id',1)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Hidden::make('doctor_id')
                            ->default(fn () => auth()->user()->id)
                            ->dehydrated(true),
                        Select::make('patient_id')
                            ->label('Пациент')
                            ->disabled()
                            ->relationship('patient', 'full_name') // yoki kerakli atribut
                            ->default(request()->get('patient_id'))
                            ->required()
                            ->columnSpan(12),
                        Select::make('medical_history_id')
                            ->label('План осмотра')
                            ->reactive()
                            ->options(
                                \App\Models\MedicalHistory::all()->pluck('created_at', 'id')->mapWithKeys(function ($createdAt, $id) {
                                    $formattedId = str_pad('№'.$id, 10); // 10 ta belgigacha bo‘sh joy qo‘shiladi
                                        return [$id => $formattedId . \Carbon\Carbon::parse($createdAt)->format('d.m.Y H:i')];
                                    })
                            )
                            ->required()
                            ->columnSpan(4),
                        Repeater::make('procedureDetails')
                                                ->label('')
                                                ->defaultItems(1)
                                                ->relationship('procedureDetails')
                                                ->schema([
                                                    Select::make('procedure_id')
                                                        ->label('Тип процедура')
                                                        ->options(function (Get $get, $state, $context) {
                                                            // Foydalanuvchi tanlagan barcha inspection_id larni to'plab olamiz
                                                            $selectedIds = collect($get('../../procedureDetails'))
                                                                ->pluck('procedure_id')
                                                                ->filter()
                                                                ->toArray();

                                                            // Agar bu `Select` allaqachon tanlangan bo‘lsa, uni istisno qilamiz
                                                            // Aks holda o‘zi ham option ro‘yxatdan yo‘qolib qoladi
                                                            if ($state) {
                                                                $selectedIds = array_diff($selectedIds, [$state]);
                                                            }

                                                            // Tanlanmagan inspection larni qaytaramiz
                                                            return Procedure::query()
                                                                ->whereNotIn('id', $selectedIds)
                                                                ->pluck('name', 'id');
                                                        })
                                                        ->searchable()
                                                        ->required()
                                                        ->reactive()
                                                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                            $price = Procedure::find($state)?->price_per_day ?? 0;
                                                            $set('price', $price);
                                                            $set('total_price', $price * ($get('sessions') ?? 1));
                                                            
                                                            static::recalculateTotalSum($get, $set);
                                                        })
                                                        ->columnSpan(4),

                                                    TextInput::make('price')
                                                        ->label('Цена')
                                                        ->readOnly()
                                                        ->numeric()
                                                        ->columnSpan(3),

                                                    TextInput::make('sessions')
                                                        ->label('Кол сеансов')
                                                        ->numeric()
                                                        ->default(1)
                                                        ->required()
                                                        ->reactive()
                                                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                                            $set('total_price', ($get('price') ?? 0) * ($state ?? 1));
                                                            
                                                            static::recalculateTotalSum($get, $set);
                                                        })
                                                        ->columnSpan(2),

                                                    TextInput::make('total_price')
                                                        ->label('Общая стоимость')
                                                        ->disabled()
                                                        ->numeric()
                                                        ->columnSpan(3)
                                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                                            static::recalculateTotalSum($get, $set);
                                                        }),
                                                ])
                                                ->afterStateHydrated(function (Get $get, Set $set, $state) {
                                                    foreach ($state as $index => $item) {
                                                        $price = $item['price'] ?? 0;
                                                        $sessions = $item['sessions'] ?? 1;
                                                        $total = $price * $sessions;
                                                        $set("procedureDetails.{$index}.total_price", $total);
                                                    }
                                                })
                                                ->columns(12)->columnSpan(12),
                        Placeholder::make('total_sum')
                                                    ->label('Общая стоимость (всего)')
                                                    ->content(function (Get $get) {
                                                        $items = $get('procedureDetails') ?? [];
                                                        $total = collect($items)->sum('total_price');
                                                        return number_format($total, 2, '.', ' ') . ' сум';
                                                    })
                                                    ->columnSpanFull(), 
                    ])->columnSpan(12)->columns(12),
                    Section::make()
                        ->schema([
                            DatePicker::make('admission_date')
                                ->label('Дата поступления')
                                ->default(Carbon::now())
                                ->columnSpan(6),
                            DatePicker::make('discharge_date')
                                ->label('Дата выписки')
                                ->columnSpan(6),  
                        ])->columns(12)->columnSpan(12),
                    Fieldset::make('Койка') 
                                ->relationship('medicalBed') 
                                ->schema([ 
                                    Select::make('tariff_id') 
                                        ->label('Тарифф') 
                                        ->options(function () { 
                                            return Tariff::all()->mapWithKeys(function ($tariff) { 
                                                return [$tariff->id => $tariff->name . ' - ' . number_format($tariff->daily_price, 0) . ' сум']; 
                                            }); 
                                        }) 
                                        ->reactive() 
                                        ->required()
                                        ->columnSpan(4), 

                                    Select::make('ward_id') 
                                        ->label('Палата') 
                                        ->options(function (Get $get) { 
                                            $tariffId = $get('tariff_id'); 
                                            if (!$tariffId) return []; 
                                            
                                            return Ward::where('tariff_id', $tariffId)
                                                ->get()
                                                ->mapWithKeys(function ($ward) {
                                                    // Bo'sh koygalar sonini hisoblash
                                                        
                                                    return [$ward->id => $ward->name . " ({$ward->availableBedsCount} на пустой койке)"];
                                                });
                                        }) 
                                        ->reactive() 
                                        ->required()
                                        ->visible(fn (Get $get) => filled($get('tariff_id'))) 
                                        ->columnSpan(4), 

                                    Select::make('bed_id') 
                                        ->label('На пустой койке') 
                                        ->options(function (Get $get) { 
                                            $wardId = $get('ward_id'); 
                                            if (!$wardId) return []; 
                                            
                                            return Bed::where('ward_id', $wardId)
                                                ->availableBeds()
                                                ->get()
                                                ->mapWithKeys(function ($bed) {
                                                    return [$bed->id => "Койка #{$bed->number}"];
                                                });
                                        }) 
                                        ->required()
                                        ->visible(fn (Get $get) => filled($get('ward_id'))) 
                                        ->columnSpan(4), 
                                ])->columns(12)->columnSpan(12),
                            Fieldset::make('Питание')
                                ->relationship('medicalMeal')
                                ->schema([
                                Select::make('meal_type_id')
                                    ->label('Питание')
                                    ->options(function () {
                                        return MealType::all()
                                            ->mapWithKeys(function ($meal_type) {
                                                return [$meal_type->id => $meal_type->name . ' - ' . number_format($meal_type->daily_price, 0, '.', ' ') . ' сум/kun'];
                                            });
                                    })
                                    ->reactive()
                                    ->afterStateUpdated(fn (Set $set) => $set('ward_id', null))
                                    ->columnSpan(4),
                                Group::make()
                                        ->schema([
                                            
                                            Placeholder::make('meal_name')
                                                    ->label('Название')
                                                    ->visible(fn (Get $get) => filled($get('meal_type_id')))
                                                    ->content(fn (Get $get) => MealType::find($get('meal_type_id'))->name ?? '-')->columnSpan(6),
                                            Placeholder::make('meal_daily_price')
                                                    ->label('Цена')
                                                    ->visible(fn (Get $get) => filled($get('meal_type_id')))
                                                    ->content(fn (Get $get) => MealType::find($get('meal_type_id'))->daily_price ?? '-')->columnSpan(6),
                                            Placeholder::make('meal_description')
                                                    ->label('Описание')
                                                    ->visible(fn (Get $get) => filled($get('meal_type_id')))
                                                    ->content(fn (Get $get) => MealType::find($get('meal_type_id'))->description ?? '-')->columnSpan(12),
                                    ])->columns(12)->columnSpan(8),
                                ])->columns(12)->columnSpan(12),
                                
                            Section::make('Детализация стоимости')
                                ->schema([
                                    // Proceduralar uchun hisob
                                    Placeholder::make('procedures_total')
                                        ->label('Стоимость процедур')
                                        ->content(function (Get $get) {
                                            $items = $get('procedureDetails') ?? [];
                                            $total = collect($items)->sum('total_price');
                                            return number_format($total, 0, '.', ' ') . ' сум';
                                        })
                                        ->columnSpan(6),
                                    // Koyka uchun hisob
                                    Placeholder::make('bed_total')
                                        ->label('Стоимость койки')
                                        ->content(function (Get $get) {
                                            $bedId = $get('medicalBed.bed_id');
                                            $admissionDate = $get('admission_date');
                                            $dischargeDate = $get('discharge_date');

                                            if (!$bedId || !$admissionDate || !$dischargeDate) {
                                                return '0 сум (не выбрано)';
                                            }

                                            $bed = \App\Models\Bed::with('ward.tariff')->find($bedId);
                                            if (!$bed) {
                                                return '0 сум (койка не найдена)';
                                            }

                                            $dailyPrice = $bed->ward->tariff->daily_price;
                                            $days = \Carbon\Carbon::parse($admissionDate)->diffInDays(\Carbon\Carbon::parse($dischargeDate));
                                            $total = $dailyPrice * $days;

                                            return number_format($total, 0, '.', ' ') . ' сум (' . $days . ' дней × ' . number_format($dailyPrice, 0, '.', ' ') . ')';
                                        })
                                        ->columnSpan(6),

                                    // Ovqatlanish uchun hisob
                                    Placeholder::make('meal_total')
                                        ->label('Стоимость питания')
                                        ->content(function (Get $get) {
                                            $mealTypeId = $get('medicalMeal.meal_type_id');
                                            $admissionDate = $get('admission_date');
                                            $dischargeDate = $get('discharge_date');

                                            if (!$mealTypeId || !$admissionDate || !$dischargeDate) {
                                                return '0 сум (не выбрано)';
                                            }

                                            $mealType = \App\Models\MealType::find($mealTypeId);
                                            if (!$mealType) {
                                                return '0 сум (питание не найдено)';
                                            }

                                            $dailyPrice = $mealType->daily_price;
                                            $days = \Carbon\Carbon::parse($admissionDate)->diffInDays(\Carbon\Carbon::parse($dischargeDate));
                                            $total = $dailyPrice * $days;

                                            return number_format($total, 0, '.', ' ') . ' сум (' . $days . ' дней × ' . number_format($dailyPrice, 0, '.', ' ') . ')';
                                        })
                                        ->columnSpan(6),

                                    // Umumiy summa
                                    Placeholder::make('grand_total')
                                        ->label('ОБЩАЯ СТОИМОСТЬ')
                                        ->content(function (Get $get) {
                                            // Proceduralar
                                            $proceduresItems = $get('procedureDetails') ?? [];
                                            $proceduresTotal = collect($proceduresItems)->sum('total_price');
                                            //Analizlar
                                            $labTestItems = $get('labTestHistories') ?? [];
                                            $labTestTotal = collect($labTestItems)->sum('price');

                                            // Koyka
                                            $bedTotal = 0;
                                            $bedId = $get('medicalBed.bed_id');
                                            $admissionDate = $get('admission_date');
                                            $dischargeDate = $get('discharge_date');

                                            if ($bedId && $admissionDate && $dischargeDate) {
                                                $bed = \App\Models\Bed::with('ward.tariff')->find($bedId);
                                                if ($bed) {
                                                    $days = \Carbon\Carbon::parse($admissionDate)->diffInDays(\Carbon\Carbon::parse($dischargeDate));
                                                    $bedTotal = $bed->ward->tariff->daily_price * $days;
                                                }
                                            }

                                            // Ovqatlanish
                                            $mealTotal = 0;
                                            $mealTypeId = $get('medicalMeal.meal_type_id');

                                            if ($mealTypeId && $admissionDate && $dischargeDate) {
                                                $mealType = \App\Models\MealType::find($mealTypeId);
                                                if ($mealType) {
                                                    $days = \Carbon\Carbon::parse($admissionDate)->diffInDays(\Carbon\Carbon::parse($dischargeDate));
                                                    $mealTotal = $mealType->daily_price * $days;
                                                }
                                            }

                                            $grandTotal = $proceduresTotal + $labTestTotal + $bedTotal + $mealTotal;

                                            return new \Illuminate\Support\HtmlString("
                                                <div class='bg-blue-50 p-4 rounded-lg border-2 border-blue-200'>
                                                    <div class='text-2xl font-bold text-blue-800'>
                                                        " . number_format($grandTotal, 0, '.', ' ') . " сум
                                                    </div>
                                                </div>
                                            ");
                                        })
                                        ->columnSpan(6),
                                ])
                                ->columns(12)
                                ->columnSpan(12),
            ]);
    }
    
    // public static function shouldRegisterNavigation(): bool
    // {
    //     return false;
    // }

    public static function getNavigationLabel(): string
    {
        return 'Для процедуры'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Для процедуры'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Для процедуры'; // Rus tilidagi ko'plik shakli
    }
    protected static function recalculateTotalSum(Get $get, Set $set): void
    {
        $items = $get('assignedProcedures') ?? [];
        $total = collect($items)->sum('total_price');
        $set('total_sum', $total);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status_payment_id', 1); // faqat status 1 bo'lganlar
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('patient.full_name')->label('ФИО')->searchable()->sortable(),
                TextColumn::make('total_paid')
                    ->label('Обшый сумма')
                    ->getStateUsing(function ($record) {
                        return number_format($record->getTotalCost(),0,'.',' ').' сум';
                    }),
                TextColumn::make('total_debt')
                    ->label('Долг')
                    ->color('danger')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $remaining = $record->getTotalCost() - $record->getTotalPaidAmount();
                        return number_format($remaining, 0, '.', ' ') . ' сум';
                    }),
                TextColumn::make('created_at')->searchable()->sortable(),
            ])
            ->actions([
                Action::make('add_payment')
                        ->label('Оплата')
                        ->icon('heroicon-o-credit-card')
                        ->color('success')
                        ->modalWidth(MaxWidth::TwoExtraLarge)
                        ->form([
                            Section::make('Данные платежа')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('total_cost')
                                                ->label('Общие')
                                                ->disabled()
                                                ->default(function ($record) {
                                                    return number_format($record->getTotalCost(), 0, '.', ' ') . ' сум';
                                                }),
                                                
                                            TextInput::make('total_paid')
                                                ->label('Оплачено')
                                                ->disabled()
                                                ->default(function ($record) {
                                                    return number_format($record->getTotalPaidAmount(), 0, '.', ' ') . ' сум';
                                                }),
                                        ]),
                                        
                                    TextInput::make('remaining')
                                        ->label('Остаток')
                                        ->disabled()
                                        ->default(function ($record) {
                                            $remaining = $record->getTotalCost() - $record->getTotalPaidAmount();
                                            return number_format($remaining, 0, '.', ' ') . ' сум';
                                        }),
                                ]),
                                
                            Section::make('')
                                ->schema([
                                    TextInput::make('amount')
                                        ->label('Сумма')
                                        ->numeric()
                                        ->required()
                                        ->maxValue(function ($record) {
                                            $remaining = $record->getTotalCost() - $record->getTotalPaidAmount();
                                            return $remaining;
                                        })
                                        ->suffix('сум')
                                        ->placeholder('0.00')
                                        ->live()
                                        ->afterStateUpdated(function ($state, $set, $record) {
                                            $remaining = $record->getTotalCost() - $record->getTotalPaidAmount();
                                            if ($state > $remaining) {
                                                $set('amount', $remaining);
                                            }
                                        }),
                                    Select::make('payment_type_id')
                                        ->label('Тип оплаты')
                                        ->options(PaymentType::all()->pluck('name', 'id'))
                                        ->required(),
                                        
                                    Textarea::make('description')
                                        ->label('Izoh')
                                        ->placeholder('Коммент')
                                        ->maxLength(255)
                                        ->rows(3),
                                ]),
                        ])
                        ->action(function (array $data, $record) {
                            // To'lovni saqlash
                            \App\Models\Payment::create([
                                'patient_id' => $record->patient_id,
                                'assigned_procedure_id' => $record->id,
                                'amount' => $data['amount'],
                                'payment_type_id' => $data['payment_type_id'],
                                'description' => $data['description'] ?? null,
                            ]);

                            // Muvaffaqiyat xabari
                            Notification::make()
                                ->title('Выплата успешно добавлена!')
                                ->success()
                                ->body("Оплата: " . number_format($data['amount'], 2) . " сум")
                                ->send();
                        })
                        ->modalHeading('Оплата')
                        ->modalSubmitActionLabel('Сохранить')
                        ->modalCancelActionLabel('Отмена'),
            ])
            ->filters([
                //
            ]);
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
            'index' => Pages\ListAssignedProcedures::route('/'),
            'create' => Pages\CreateAssignedProcedure::route('/create'),
            'edit' => Pages\EditAssignedProcedure::route('/{record}/edit'),
        ];
    }
}

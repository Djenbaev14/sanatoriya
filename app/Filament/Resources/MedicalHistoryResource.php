<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalHistoryResource\Pages;
use App\Filament\Resources\MedicalHistoryResource\RelationManagers;
use App\Models\AssignedProcedure;
use App\Models\Bed;
use App\Models\DailyService;
use App\Models\Inspection;
use App\Models\LabTest;
use App\Models\MealType;
use App\Models\MedicalHistory;
use App\Models\MedicalMeal;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Procedure;
use App\Models\ReturnedProcedure;
use App\Models\Tariff;
use App\Models\Ward;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class MedicalHistoryResource extends Resource
{
    protected static ?string $model = MedicalHistory::class;
    protected static ?string $navigationGroup = 'Касса';
    protected static ?int $navigationSort = 3;
    public static function form(Form $form): Form{
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('patient_id')
                            ->label('Пациент')
                            ->disabled()
                            ->relationship('patient', 'full_name') // yoki kerakli atribut
                            ->default(request()->get('patient_id'))
                            ->required()
                            ->columnSpan(12),
                        Hidden::make('doctor_id')
                            ->default(fn () => auth()->user()->id)
                            ->dehydrated(true),
                            
                        TextInput::make('height')
                            ->label('рост')
                            ->required()
                            ->suffix('sm')
                            ->columnSpan(4),
                        TextInput::make('weight')
                            ->label('вес')
                            ->suffix('kg')
                            ->required()
                            ->columnSpan(4),
                        TextInput::make('temperature')
                            ->label('температура')
                            ->suffix('°C')
                            ->required()
                            ->columnSpan(4),
                        Select::make('disability_types')
                            ->label('Nogironlik turi')
                            ->multiple()
                            ->options([
                                'no' => "Yo'q",
                                'physical' => 'Jismoniy',
                                'visual' => 'Ko‘rish',
                                'hearing' => 'Eshitish',
                                'mental' => 'Aqliy',
                                'speech' => 'Nutq',
                                'other' => 'Boshqa',
                            ])
                            ->required()
                            ->searchable()
                            ->columnSpan(4),
                        Select::make('referred_from')
                            ->label('Qayerdan yuborilgan?')
                            ->options([
                                'clinic' => 'Poliklinika',
                                'hospital' => 'Shifoxona',
                                'emergency' => 'Tez yordam',
                                'self' => 'O‘zi kelgan',
                                'other' => 'Boshqa',
                            ])
                            ->searchable()
                            ->required()
                            ->columnSpan(4),
                        Select::make('transport_type')
                            ->label('Qanday transportda keldi?')
                            ->options([
                                'ambulance' => 'Tez yordam',
                                'family' => 'Yaqinlari olib kelgan',
                                'self' => 'O‘zi kelgan',
                                'taxi' => 'Taksi',
                                'other' => 'Boshqa',
                            ])
                            ->searchable()
                            ->required()
                            ->columnSpan(4),
                        Textarea::make('side_effects')
                            ->label("Dorilarning nojo'ya ta'siri")
                            ->rows(4)
                            ->placeholder("Masalan: Allergik toshmalar, bosh aylanishi...")
                            ->columnSpan(4),
                        Radio::make('is_emergency')
                            ->required()
                            ->label('Shoshilinch holatda keltirildimi?')
                            ->options([
                                '1' => 'ha',
                                '0'=> "yo'q",
                            ])
                            ->columnSpan(4),
            ])->columns(12)->columnSpan(12),
            Section::make()
                        ->schema([
                            DateTimePicker::make('admission_date')
                                ->label('Дата поступления')
                                ->reactive()
                                ->default(Carbon::now())
                                ->columnSpan(6),
                            DatePicker::make('discharge_date')
                                ->label('Дата выписки')
                                ->reactive()
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
                                        ->reactive() 
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
                                                    ->content(fn (Get $get) => number_format(MealType::find($get('meal_type_id'))->daily_price,0) ?? '-')->columnSpan(6),
                                            Placeholder::make('meal_description')
                                                    ->label('Описание')
                                                    ->visible(fn (Get $get) => filled($get('meal_type_id')))
                                                    ->content(fn (Get $get) => MealType::find($get('meal_type_id'))->description ?? '-')->columnSpan(12),
                                    ])->columns(12)->columnSpan(8),
                                ])->columns(12)->columnSpan(12),
                                
                            Section::make('Общая стоимость')
                                ->schema([
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
                                            if (!$bed || !$bed->ward || !$bed->ward->tariff) {
                                                return '0 сум (койка не найдена)';
                                            }

                                            $dailyPrice = $bed->ward->tariff->daily_price;

                                            $admission = \Carbon\Carbon::parse($admissionDate);
                                            $discharge = \Carbon\Carbon::parse($dischargeDate);

                                            $days = $admission->diffInDays($discharge) + 1;

                                            // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                            if ($admission->format('H:i') > '12:00' && $days > 0) {
                                                $days -= 1;
                                            }

                                            // Kamida 1 kun hisoblash
                                            $days = max($days, 1);

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
                                            

                                            $admission = \Carbon\Carbon::parse($admissionDate);
                                            $discharge = \Carbon\Carbon::parse($dischargeDate);

                                            $days = $admission->diffInDays($discharge) + 1;

                                            // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                            if ($admission->format('H:i') > '12:00' && $days > 0) {
                                                $days -= 1;
                                            }
                                            // Kamida 1 kun hisoblash
                                            $days = max($days, 1);

                                            $total = $dailyPrice * $days;
                                            return number_format($total, 0, '.', ' ') . ' сум (' . $days . ' дней × ' . number_format($dailyPrice, 0, '.', ' ') . ')';
                                        })
                                        ->columnSpan(6),

                                    // Umumiy summa
                                    Placeholder::make('grand_total')
                                        ->label('ОБЩАЯ СТОИМОСТЬ')
                                        ->content(function (Get $get) {

                                            // Koyka
                                            $bedTotal = 0;
                                            $bedId = $get('medicalBed.bed_id');
                                            $admissionDate = $get('admission_date');
                                            $dischargeDate = $get('discharge_date');

                                            if ($bedId && $admissionDate && $dischargeDate) {
                                                $bed = \App\Models\Bed::with('ward.tariff')->find($bedId);
                                                if ($bed) {
                                                    $admission = \Carbon\Carbon::parse($admissionDate);
                                                    $discharge = \Carbon\Carbon::parse($dischargeDate);

                                                    $days = $admission->diffInDays($discharge) + 1;

                                                    // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                                    if ($admission->format('H:i') > '12:00' && $days > 0) {
                                                        $days -= 1;
                                                    }
                                                    // Kamida 1 kun hisoblash
                                                    $days = max($days, 1);
                                                    $bedTotal = $bed->ward->tariff->daily_price * $days;
                                                }
                                            }

                                            // Ovqatlanish
                                            $mealTotal = 0;
                                            $mealTypeId = $get('medicalMeal.meal_type_id');

                                            if ($mealTypeId && $admissionDate && $dischargeDate) {
                                                $mealType = \App\Models\MealType::find($mealTypeId);
                                                if ($mealType) {
                                                    
                                                    $admission = \Carbon\Carbon::parse($admissionDate);
                                                    $discharge = \Carbon\Carbon::parse($dischargeDate);

                                                    $days = $admission->diffInDays($discharge) + 1;

                                                    // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                                    if ($admission->format('H:i') > '12:00' && $days > 0) {
                                                        $days -= 1;
                                                    }
                                                    // Kamida 1 kun hisoblash
                                                    $days = max($days, 1);
                                                    $mealTotal = $mealType->daily_price * $days;
                                                }
                                            }

                                            $grandTotal =$bedTotal + $mealTotal;

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
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    
    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             TextColumn::make('patient.full_name')->label('ФИО')->searchable()->sortable(),
    //             TextColumn::make('total_paid')
    //                 ->label('Обшый сумма')
    //                 ->getStateUsing(function ($record) {
    //                     return number_format($record->getTotalCost(),0,'.',' ').' сум';
    //                 }),
    //             TextColumn::make('created_at')->searchable()->sortable(),
    //         ])
    //         ->filters([
    //             //
    //         ])
    //         ->actions([
    //             Action::make('add_payment')
    //                     ->label('Оплата')
    //                     ->icon('heroicon-o-credit-card')
    //                     ->color('success')
    //                     ->modalWidth(MaxWidth::TwoExtraLarge)
    //                     ->form([
    //                         Section::make('Данные платежа')
    //                             ->schema([
    //                                 Grid::make(2)
    //                                     ->schema([
    //                                         TextInput::make('total_cost')
    //                                             ->label('Общие')
    //                                             ->disabled()
    //                                             ->default(function ($record) {
    //                                                 return number_format($record->getTotalCost(), 0, '.', ' ') . ' сум';
    //                                             }),
                                                
    //                                         TextInput::make('total_paid')
    //                                             ->label('Оплачено')
    //                                             ->disabled()
    //                                             ->default(function ($record) {
    //                                                 return number_format($record->getTotalPaidAmount(), 0, '.', ' ') . ' сум';
    //                                             }),
    //                                     ]),
                                        
    //                                 TextInput::make('remaining')
    //                                     ->label('Остаток')
    //                                     ->disabled()
    //                                     ->default(function ($record) {
    //                                         $remaining = $record->getTotalCost() - $record->getTotalPaidAmount();
    //                                         return number_format($remaining, 0, '.', ' ') . ' сум';
    //                                     }),
    //                             ]),
                                
    //                         Section::make('')
    //                             ->schema([
    //                                 TextInput::make('amount')
    //                                     ->label('Сумма')
    //                                     ->numeric()
    //                                     ->required()
    //                                     ->minValue(0.01)
    //                                     ->step(0.01)
    //                                     ->suffix('сум')
    //                                     ->placeholder('0.00')
    //                                     ->live()
    //                                     ->afterStateUpdated(function ($state, $set, $record) {
    //                                         $remaining = $record->getTotalCost() - $record->getTotalPaidAmount();
    //                                         if ($state > $remaining) {
    //                                             $set('amount', $remaining);
    //                                         }
    //                                     }),
    //                                 Select::make('payment_type_id')
    //                                     ->label('Тип оплаты')
    //                                     ->options(PaymentType::all()->pluck('name', 'id'))
    //                                     ->required(),
                                        
    //                                 Textarea::make('description')
    //                                     ->label('Izoh')
    //                                     ->placeholder('Коммент')
    //                                     ->maxLength(255)
    //                                     ->rows(3),
    //                             ]),
    //                     ])
    //                     ->action(function (array $data, $record) {
    //                         // To'lovni saqlash
    //                         \App\Models\Payment::create([
    //                             'patient_id' => $record->patient_id,
    //                             'lab_test_history_id' => $record->id,
    //                             'amount' => $data['amount'],
    //                             'payment_type_id' => $data['payment_type_id'],
    //                             'description' => $data['description'] ?? null,
    //                         ]);

    //                         // Muvaffaqiyat xabari
    //                         Notification::make()
    //                             ->title('Выплата успешно добавлена!')
    //                             ->success()
    //                             ->body("Оплата: " . number_format($data['amount'], 2) . " сум")
    //                             ->send();
    //                     })
    //                     ->modalHeading('Оплата')
    //                     ->modalSubmitActionLabel('Сохранить')
    //                     ->modalCancelActionLabel('Отмена'),
    //         ])
    //         ->bulkActions([
    //             Tables\Actions\BulkActionGroup::make([
    //                 Tables\Actions\DeleteBulkAction::make(),
    //             ]),
    //         ]);
    // }

    public static function getNavigationLabel(): string
    {
        return 'Истории'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Истории'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Истории'; // Rus tilidagi ko'plik shakli
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
            'index' => Pages\ListMedicalHistories::route('/'),
            'create' => Pages\CreateMedicalHistory::route('/create'),
            'edit' => Pages\EditMedicalHistory::route('/{record}/edit'),
            'view' => Pages\viewMedicalHistory::route('/{record}'),
        ];
    }
}

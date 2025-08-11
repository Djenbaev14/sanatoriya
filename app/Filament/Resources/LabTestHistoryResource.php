<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LabTestHistoryResource\Pages;
use App\Filament\Resources\LabTestHistoryResource\RelationManagers;
use App\Models\LabTest;
use App\Models\LabTestHistory;
use App\Models\Patient;
use App\Models\PaymentType;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class LabTestHistoryResource extends Resource
{
    protected static ?string $model = LabTestHistory::class;

    public static function form(Form $form): Form{
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
                                                ->required()
                                                ->default(request()->get('medical_history_id'))
                                                ->label('История болезно')
                                                ->options(function (Get $get, $state) {
                                                    $patientId = $get('patient_id');

                                                    if (!$patientId) return [];

                                                    $query = \App\Models\MedicalHistory::where('patient_id', $patientId)
                                                        ->doesntHave('labTestHistory');

                                                    // 👇 edit holatida tanlangan qiymat chiqsin
                                                    if ($state) {
                                                        $query->orWhere('id', $state); // yoki ->orWhere('id', $state) agar 'id' saqlanayotgan bo‘lsa
                                                    }

                                                    return $query->get()->mapWithKeys(function ($history) {
                                                        $formattedId = str_pad('№' . $history->number, 10);
                                                        $formattedDate = \Carbon\Carbon::parse($history->created_at)->format('d.m.Y H:i');
                                                        return [$history->id => $formattedId . ' - ' . $formattedDate];
                                                    });
                                                })
                                                ->required()
                                                ->columnSpan(4),
                                            // Repeater::make('labTestDetails')
                                            //     ->relationship('labTestDetails')
                                            //     ->label('')
                                            //     ->default([])
                                            //     ->disableItemDeletion()
                                            //     ->disableItemCreation()
                                            //     ->schema([
                                            //         Checkbox::make('selected')
                                            //             ->label('')
                                            //             ->columnSpan(1)
                                            //             ->reactive(),
                                            //         TextInput::make('lab_test_name')
                                            //             ->label('')
                                            //             ->columnSpan(6)
                                            //             ->disabled(),
                                            //         Hidden::make('lab_test_id'),

                                            //         Hidden::make('sessions')
                                            //             ->default(1),

                                            //         TextInput::make('price')
                                            //             ->label('')
                                            //             ->visible(fn () => !auth()->user()->hasRole('Доктор'))
                                            //             ->columnSpan(6)
                                            //             ->readOnly(),
                                            //     ])
                                            //     ->columns(24)
                                            //     ->columnSpanFull()
                                            //     ->afterStateHydrated(function (Get $get, Set $set, $state) {
                                            //         $medicalHistoryId = $get('medical_history_id');

                                            //         $existinglabTestIds = collect($state)->pluck('lab_test_id')->filter()->unique()->toArray();

                                            //         $lab_tests = collect();

                                            //         // 1. Agar MedicalInspection bo‘lsa, shunga qarab procedure larni olamiz
                                            //         if ($medicalHistoryId) {
                                            //             $mkbClassId = \App\Models\MedicalInspection::where('medical_history_id', $medicalHistoryId)
                                            //                 ->value('mkb_class_id');

                                            //             if ($mkbClassId) {

                                            //                 $lab_tests = \App\Models\LabTest::whereHas('LabTestMkbs', function ($query) use ($mkbClassId): void {
                                            //                     $query->where('mkb_class_id', $mkbClassId);
                                            //                 })->get(['id', 'name', 'price']);

                                            //             }
                                            //         }

                                            //         // 2. Agar MedicalInspection yo‘q bo‘lsa yoki natija topilmasa, eski saqlangan procedure_id lar bo‘yicha olish
                                            //         if ($lab_tests->isEmpty() && !empty($existinglabTestIds)) {
                                            //             $lab_tests = \App\Models\LabTest::whereIn('id', $existinglabTestIds)
                                            //                 ->get(['id', 'name', 'price']);
                                            //         }

                                            //         // 3. Eski state'ni id bo‘yicha indexlab olamiz
                                            //         $oldLabTestById = collect($state)->keyBy('lab_test_id');

                                            //         $updatedLabTests = $lab_tests->map(function ($item) use ($oldLabTestById) {
                                            //             $existing = $oldLabTestById->get($item->id);

                                            //             return [
                                            //                 'lab_test_id' => $item->id,
                                            //                 'lab_test_name' => $item->name,
                                            //                 'sessions' => $existing['sessions'] ?? 1,
                                            //                 'price' => $existing['price'] ?? $item->price,
                                            //                 'selected' => $existing ? true : false, // bu doim eski tanlanganlar uchun true bo‘ladi
                                            //             ];
                                            //         });

                                            //         $set('labTestDetails', $updatedLabTests->toArray());
                                            //     })
                                            //     ->saveRelationshipsUsing(function (Repeater $component, Model $record, array $state) {
                                            //         // Mavjud yozuvlarni olish
                                            //         $existinglabTests = $record->labTestDetails()->pluck('lab_test_id')->toArray();
                                                    
                                            //         // Selected bo'lgan proceduralar
                                            //         $selectedLabTests = collect($state)->where('selected', true);
                                            //         $selectedLabTestIds = $selectedLabTests->pluck('lab_test_id')->toArray();
                                                    
                                            //         // Selected bo'lgan proceduralarni saqlash/yangilash
                                            //         foreach ($selectedLabTests as $lab_test) {
                                            //             $record->labTestDetails()->updateOrCreate(
                                            //                 ['lab_test_id' => $lab_test['lab_test_id']],
                                            //                 [
                                            //                     'sessions' => $lab_test['sessions'] ?? 1,
                                            //                     'price' => $lab_test['price'],
                                            //                 ]
                                            //             );
                                            //         }
                                                    
                                            //         // Selected bo'lmagan lekin mavjud bo'lgan yozuvlarni o'chirish
                                            //         $toDelete = array_diff($existinglabTests, $selectedLabTestIds);
                                            //         if (!empty($toDelete)) {
                                            //             $record->labTestDetails()->whereIn('lab_test_id', $toDelete)->delete();
                                            //         }
                                            //     }),

                                            // Hidden::make('labTestDetails')->dehydrateStateUsing(function ($state, Get $get) {
                                            //         return $get('labTestDetails');
                                            // }),
                                            // Placeholder::make('total_sum')
                                            //     ->label('Общая стоимость (всего)')
                                            //     ->content(function (Get $get) {
                                            //         $items = $get('labTestDetails') ?? [];
                                            //         $total = collect($items)->sum(function ($item) {
                                            //             return ($item['selected'] ?? false) ? ($item['price'] ?? 0) : 0;
                                            //         });

                                            //         return number_format($total, 2, '.', ' ') . ' сум';
                                            //     })
                                            //     ->visible(fn () => !auth()->user()->hasRole('Доктор'))
                                            //     ->reactive()
                                            //     ->columnSpanFull(),
                                            Repeater::make('labTestDetails')
                                                ->label('Анализ')
                                                ->relationship('labTestDetails')
                                                ->defaultItems(1)
                                                ->schema([
                                                    Select::make('lab_test_id')
                                                        ->label('Тип анализ')
                                                        ->options(function (Get $get, $state, $context) {
                                                            // Foydalanuvchi tanlagan barcha inspection_id larni to'plab olamiz
                                                            $selectedIds = collect($get('../../labTestDetails'))
                                                                ->pluck('lab_test_id')
                                                                ->filter()
                                                                ->toArray();

                                                            // Agar bu `Select` allaqachon tanlangan bo‘lsa, uni istisno qilamiz
                                                            // Aks holda o‘zi ham option ro‘yxatdan yo‘qolib qoladi
                                                            if ($state) {
                                                                $selectedIds = array_diff($selectedIds, [$state]);
                                                            }

                                                            // Tanlanmagan inspection larni qaytaramiz
                                                            return LabTest::query()
                                                                ->whereNotIn('id', $selectedIds)
                                                                ->pluck('name', 'id');
                                                        })
                                                        ->searchable()
                                                        ->required()
                                                        ->reactive()
                                                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                            $patientId = $get('../../patient_id'); // yoki `request()->get('patient_id')`
                                                                if (!$patientId || !$state) {
                                                                    $set('price', 0);
                                                                    return;
                                                                }

                                                                $isForeign = Patient::find($patientId)?->is_foreign ?? 0;

                                                                $lab_test = LabTest::find($state);
                                                                $price = $isForeign == 1 ? $lab_test?->price_foreign : $lab_test?->price;

                                                                $set('price', $price ?? 0);
                                                                $set('total_price', $price * ($get('sessions') ?? 1));
                                                                
                                                            static::recalculateTotalSum($get, $set);
                                                        })
                                                        ->columnSpan(4),

                                                    TextInput::make('price')
                                                        ->label('Цена')
                                                        ->numeric()
                                                        ->reactive()
                                                        ->visible(fn () => !auth()->user()->hasRole('Доктор'))
                                                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                                            // sessions maydonini olamiz
                                                            $sessions = $get('sessions') ?? 1;

                                                            // total_price ni hisoblab yangilaymiz
                                                            $set('total_price', $state * $sessions);

                                                            // umumiy summa qayta hisoblanadi
                                                            static::recalculateTotalSum($get, $set);
                                                        })
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
                                                        ->visible(fn () => !auth()->user()->hasRole('Доктор'))
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
                                                        $set("labTestDetails.{$index}.total_price", $total);
                                                    }
                                                })
                                                ->columns(12)->columnSpan(12),
                                                Placeholder::make('total_sum')
                                                    ->label('Общая стоимость (всего)')
                                                    ->visible(fn () => !auth()->user()->hasRole('Доктор'))
                                                    ->content(function (Get $get) {
                                                        $items = $get('labTestDetails') ?? [];
                                                        $total = collect($items)->sum('total_price');
                                                        return number_format($total, 2, '.', ' ') . ' сум';
                                                    })
                                                    ->columnSpanFull(), 
                    ])->columnSpan(12)->columns(12)
            ]);
    }
    protected static function recalculateTotalSum(Get $get, Set $set): void
    {
        $items = $get('labTestHistories') ?? [];
        $total = collect($items)->sum('total_price');
        $set('total_sum', $total);
    }
    public static function getNavigationLabel(): string
    {
        return 'Для анализа'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Для анализа'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Для анализа'; // Rus tilidagi ko'plik shakli
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    
    // public static function getEloquentQuery(): Builder
    // {
    //     return static::getModel()::query()
    //         ->where('status_payment_id', 2); // faqat status 1 bo'lganlar
    // }
    public static function table(Table $table): Table
    {
        return $table
        
            ->query(
                LabTestHistory::query()
                    ->where('status_payment_id', 2)
            )
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
            ->defaultPaginationPageOption(50)
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
                                        ->label('Комментарий')
                                        ->placeholder('Коммент')
                                        ->maxLength(255)
                                        ->rows(3),
                                ]),
                        ])
                        ->action(function (array $data, $record) {
                            // To'lovni saqlash
                            \App\Models\Payment::create([
                                'patient_id' => $record->patient_id,
                                'lab_test_history_id' => $record->id,
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
            'index' => Pages\ListLabTestHistories::route('/'),
            'create' => Pages\CreateLabTestHistory::route('/create'),
            'edit' => Pages\EditLabTestHistory::route('/{record}/edit'),
            'view' => Pages\ViewLabTestHistory::route('/{record}'),
        ];
    }
}

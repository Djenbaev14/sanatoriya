<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignedProcedureResource\Pages;
use App\Filament\Resources\AssignedProcedureResource\RelationManagers;
use App\Models\AssignedProcedure;
use App\Models\Bed;
use App\Models\Inspection;
use App\Models\MealType;
use App\Models\MedicalBed;
use App\Models\MedicalMeal;
use App\Models\Mkb;
use App\Models\MkbClass;
use App\Models\Patient;
use App\Models\PaymentType;
use App\Models\Procedure;
use App\Models\Tariff;
use App\Models\Ward;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
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

class AssignedProcedureResource extends Resource
{
    protected static ?string $model = AssignedProcedure::class;


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
                            ->required()
                            ->default(request()->get('medical_history_id'))
                            ->label('История болезно')
                            ->reactive()
                            ->options(function (Get $get, $state) {
                                $patientId = $get('patient_id');

                                if (!$patientId) return [];

                                $query = \App\Models\MedicalHistory::where('patient_id', $patientId)
                                    ->doesntHave('assignedProcedure');

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
                        // Repeater::make('procedureDetails')
                        //     ->relationship('procedureDetails')
                        //     ->label('')
                        //     ->default([])
                        //     ->disableItemDeletion()
                        //     ->disableItemCreation()
                        //     ->schema([
                        //         Checkbox::make('selected')
                        //             ->label('')
                        //             ->columnSpan(1)
                        //             ->reactive(),
                        //         TextInput::make('procedure_name')
                        //             ->label('')
                        //             ->columnSpan(6)
                        //             ->disabled(),
                        //         Hidden::make('procedure_id'),

                        //         TextInput::make('price')
                        //             ->label('')
                        //             ->visible(fn () => !auth()->user()->hasRole('Доктор'))
                        //             ->columnSpan(6)
                        //             ->readOnly(),

                        //         TextInput::make('sessions')
                        //             ->numeric()
                        //             ->label('')
                        //             ->columnSpan(3)
                        //             ->default(2)
                        //             ->reactive()
                        //             ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        //                 $price = $get('price') ?? 0;
                        //                 $set('total_price', (float)$price * (int)$state);
                        //             }),

                        //         TextInput::make('total_price')
                        //             ->label('')
                        //             ->visible(fn () => !auth()->user()->hasRole('Доктор'))
                        //             ->columnSpan(8)
                        //             ->disabled(),
                        //     ])
                        //     ->columns(24)
                        //     ->columnSpanFull()
                        //     ->afterStateHydrated(function (Get $get, Set $set, $state) {
                        //         $medicalHistoryId = $get('medical_history_id');

                        //         $existingProcedureIds = collect($state)->pluck('procedure_id')->filter()->unique()->toArray();

                        //         $procedures = collect();

                        //         // 1. Agar MedicalInspection bo‘lsa, shunga qarab procedure larni olamiz
                        //         if ($medicalHistoryId) {
                        //             $mkbClassId = \App\Models\MedicalInspection::where('medical_history_id', $medicalHistoryId)
                        //                 ->value('mkb_class_id');

                        //             if ($mkbClassId) {

                        //                 $procedures = \App\Models\Procedure::whereHas('procedureMkbs', function ($query) use ($mkbClassId): void {
                        //                     $query->where('mkb_class_id', $mkbClassId);
                        //                 })->get(['id', 'name', 'price_per_day']);

                        //             }
                        //         }

                        //         // 2. Agar MedicalInspection yo‘q bo‘lsa yoki natija topilmasa, eski saqlangan procedure_id lar bo‘yicha olish
                        //         if ($procedures->isEmpty() && !empty($existingProcedureIds)) {
                        //             $procedures = \App\Models\Procedure::whereIn('id', $existingProcedureIds)
                        //                 ->get(['id', 'name', 'price_per_day']);
                        //         }

                        //         // 3. Eski state'ni id bo‘yicha indexlab olamiz
                        //         $oldProcedureById = collect($state)->keyBy('procedure_id');

                        //         $updatedProcedures = $procedures->map(function ($item) use ($oldProcedureById) {
                        //             $existing = $oldProcedureById->get($item->id);

                        //             return [
                        //                 'procedure_id' => $item->id,
                        //                 'procedure_name' => $item->name,
                        //                 'price' => $existing['price'] ?? $item->price_per_day,
                        //                 'sessions' => $existing['sessions'] ?? 1,
                        //                 'selected' => $existing ? true : false, // bu doim eski tanlanganlar uchun true bo‘ladi
                        //                 'total_price' => ($existing['price'] ?? $item->price_per_day) * ($existing['sessions'] ?? 1),
                        //             ];
                        //         });

                        //         $set('procedureDetails', $updatedProcedures->toArray());
                        //     })
                        //     ->saveRelationshipsUsing(function (Repeater $component, Model $record, array $state) {
                        //         // Mavjud yozuvlarni olish
                        //         $existingProcedures = $record->procedureDetails()->pluck('procedure_id')->toArray();
                                
                        //         // Selected bo'lgan proceduralar
                        //         $selectedProcedures = collect($state)->where('selected', true);
                        //         $selectedProcedureIds = $selectedProcedures->pluck('procedure_id')->toArray();
                                
                        //         // Selected bo'lgan proceduralarni saqlash/yangilash
                        //         foreach ($selectedProcedures as $procedure) {
                        //             $record->procedureDetails()->updateOrCreate(
                        //                 ['procedure_id' => $procedure['procedure_id']],
                        //                 [
                        //                     'sessions' => $procedure['sessions'] ?? 1,
                        //                     'price' => $procedure['price'],
                        //                     'total_price' => $procedure['total_price'],
                        //                 ]
                        //             );
                        //         }
                                
                        //         // Selected bo'lmagan lekin mavjud bo'lgan yozuvlarni o'chirish
                        //         $toDelete = array_diff($existingProcedures, $selectedProcedureIds);
                        //         if (!empty($toDelete)) {
                        //             $record->procedureDetails()->whereIn('procedure_id', $toDelete)->delete();
                        //         }
                        //     }),

                        // Hidden::make('procedureDetails')->dehydrateStateUsing(function ($state, Get $get) {
                        //         return $get('procedureDetails');
                        // }),
                        // Placeholder::make('total_sum')
                        //     ->label('Общая стоимость (всего)')
                        //     ->content(function (Get $get) {
                        //         $items = $get('procedureDetails') ?? [];
                        //         $total = collect($items)->sum(function ($item) {
                        //             return ($item['selected'] ?? false) ? ($item['total_price'] ?? 0) : 0;
                        //         });

                        //         return number_format($total, 2, '.', ' ') . ' сум';
                        //     })
                        //     ->visible(fn () => !auth()->user()->hasRole('Доктор'))
                        //     ->reactive()
                        //     ->columnSpanFull(),



                        Repeater::make('procedureDetails')
                                                ->label('')
                                                ->defaultItems(1)
                                                ->relationship('procedureDetails')
                                                ->schema([
                                                    Select::make('procedure_id')
                                                        ->label('Тип процедура')
                                                        ->options(function (Get $get, $state, $context) {
                                                            $selectedIds = collect($get('../../procedureDetails'))
                                                                ->pluck('procedure_id')
                                                                ->filter()
                                                                ->toArray();

                                                            if ($state) {
                                                                $selectedIds = array_diff($selectedIds, [$state]);
                                                            }

                                                            return \App\Models\Procedure::query()
                                                                ->whereNotIn('id', $selectedIds)
                                                                ->pluck('name', 'id');
                                                        })
                                                        ->searchable()
                                                        ->required()
                                                        ->reactive()
                                                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                            $patientId = $get('../../patient_id');
                                                            if (!$patientId || !$state) {
                                                                $set('price', 0);
                                                                return;
                                                            }

                                                            $isForeign = \App\Models\Patient::find($patientId)?->is_foreign;
                                                            $procedure = \App\Models\Procedure::find($state);

                                                            $price = $isForeign == 1 ? $procedure->price_foreign : $procedure->price_per_day;
                                                            $set('price', $price ?? 0);
                                                            $set('total_price', $price * ($get('sessions') ?? 1));

                                                            static::recalculateTotalSum($get, $set);
                                                        })
                                                        ->columnSpan(5),
                                                    Select::make('executor_id')
                                                        ->label('Исполнитель')
                                                        ->nullable()
                                                        ->options(function (Get $get) {
                                                            $procedureId = $get('procedure_id');

                                                            if (!$procedureId) {
                                                                return [];
                                                            }

                                                            // Proseduraga biriktirilgan foydalanuvchilarni to‘g‘ridan-to‘g‘ri olamiz
                                                            $users = \App\Models\Procedure::find($procedureId)?->users;

                                                            if (!$users || $users->isEmpty()) {
                                                                return [];
                                                            }

                                                            return $users->pluck('name', 'id');
                                                        })
                                                        ->searchable()
                                                        ->reactive()
                                                        ->visible(function (Get $get) {
                                                            $procedureId = $get('procedure_id');

                                                            if (!$procedureId) {
                                                                return false;
                                                            }

                                                            $procedure = \App\Models\Procedure::find($procedureId);

                                                            // faqat is_operation=0 va is_treatment=0 bo‘lsa ko‘rsatamiz
                                                            return $procedure && $procedure->is_operation == 0 && $procedure->is_treatment == 0;
                                                        })
                                                        ->columnSpan(4),
                                                        Select::make('time_id')
    ->label('Время')
    ->options(function (Get $get, $record) {
        $procedureId = $get('procedure_id') ?? $record?->procedure_id;
        $executorId = $get('executor_id') ?? $record?->executor_id;

        if (!$procedureId || !$executorId) {
            return [];
        }

        $procedure = \App\Models\Procedure::find($procedureId);
        if (!$procedure) return [];

        return \App\Models\Time::query()
            ->where('time_category_id', $procedure->time_category_id)
            ->get()
            ->mapWithKeys(fn($time) => [
                $time->id => "{$time->start_time} - {$time->end_time}",
            ]);
    })
    ->default(fn($record) => $record?->time_id)
    ->searchable()
    ->preload()
    ->reactive()
    ->required()
    ->columnSpan(4),


                                                    // Select::make('time_id')
                                                    //     ->label('Время')
                                                    //     ->options(function (Get $get) {
                                                    //         $procedureId = $get('procedure_id');

                                                    //         if (!$procedureId) {
                                                    //             return [];
                                                    //         }

                                                    //         $procedure = \App\Models\Procedure::find($procedureId);

                                                    //         if (!$procedure || !$procedure->time_category_id) {
                                                    //             return [];
                                                    //         }

                                                    //         // Shu time_category_id bo‘yicha times jadvalidan olib chiqamiz
                                                    //         return \App\Models\Time::where('time_category_id', $procedure->time_category_id)
                                                    //             ->get()
                                                    //             ->mapWithKeys(function ($time) {
                                                    //                 return [
                                                    //                     $time->id => "{$time->start_time} - {$time->end_time}"
                                                    //                 ];
                                                    //             });
                                                    //     })
                                                    //     ->visible(function (Get $get) {
                                                    //         $procedureId = $get('procedure_id');

                                                    //         if (!$procedureId) {
                                                    //             return false;
                                                    //         }

                                                    //         $procedure = \App\Models\Procedure::find($procedureId);

                                                    //         // faqat is_operation=0 va is_treatment=0 bo‘lsa ko‘rsatamiz
                                                    //         return $procedure && $procedure->is_operation == 0 && $procedure->is_treatment == 0;
                                                    //     })
                                                    //     ->searchable()
                                                    //     ->preload()
                                                    //     ->reactive()
                                                    //     ->required()
                                                    //     ->columnSpan(4),
                                                    TextInput::make('price')
                                                        ->label('Цена')
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
                                                        ->columnSpan(4),

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
                                                        ->columnSpan(3),

                                                    TextInput::make('total_price')
                                                        ->label('Общая стоимость')
                                                        ->disabled()
                                                        ->visible(fn () => !auth()->user()->hasRole('Доктор'))
                                                        ->numeric()
                                                        ->columnSpan(4)
                                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                                            static::recalculateTotalSum($get, $set);
                                                        }),
                                                ])
                                                ->afterStateHydrated(function (Get $get, Set $set, $state) {
                                                    $recordId = $get('id');

                                                    foreach ($state as $index => $item) {
                                                        $session = \App\Models\ProcedureSession::where('assigned_procedure_id', $recordId)
                                                            ->where('procedure_id', $item['procedure_id'])
                                                            ->first();

                                                        if ($session) {
                                                            $set("procedureDetails.{$index}.executor_id", $session->executor_id);
                                                            $set("procedureDetails.{$index}.time_id", $session->time_id);
                                                        }
                                                        
                                                        $price = $item['price'] ?? 0;
                                                        $sessions = $item['sessions'] ?? 1;
                                                        $total = $price * $sessions;
                                                        $set("procedureDetails.{$index}.total_price", $total);
                                                    }
                                                })

                                                ->columns(24)->columnSpan(24),
                                                Placeholder::make('total_sum')
                                                    ->label('Общая стоимость (всего)')
                                                    ->visible(fn () => !auth()->user()->hasRole('Доктор'))
                                                    ->content(function (Get $get) {
                                                        $items = $get('procedureDetails') ?? [];
                                                        $total = collect($items)->sum('total_price');
                                                        return number_format($total, 2, '.', ' ') . ' сум';
                                                    })
                                                    ->columnSpanFull(), 
                    ])->columnSpan(24)->columns(24),
                                                ]);
    }
//     public static function afterCreate(Form $form, AssignedProcedure $record): void
//     {
//         // medical_history_id orqali MedicalBed mavjud bo‘lsa, yangilaymiz
//         $medicalBed = MedicalBed::firstOrNew([
//             'medical_history_id' => $record->medical_history_id,
//         ]);

//         $data = $form->getRawState();

//         $medicalBed->tariff_id = $data['tariff_id'] ?? null;
//         $medicalBed->ward_id = $data['ward_id'] ?? null;
//         $medicalBed->bed_id = $data['bed_id'] ?? null;
//         $medicalBed->save();
        
//         $medicalMeal = MedicalMeal::firstOrNew([
//             'medical_history_id' => $record->medical_history_id,
//         ]);

//         $medicalMeal->meal_type_id = $data['meal_type_id'] ?? null;
//         $medicalMeal->save();
// }
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    
    protected static function recalculateTotalSum(Get $get, Set $set): void
    {
        $items = $get('assignedProcedures') ?? [];
        $total = collect($items)->sum('total_price');
        $set('total_sum', $total);
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
            ->defaultPaginationPageOption(50)
            ->actions([
                Action::make('add_payment')
                        ->label('Оплата')
                        ->icon('heroicon-o-credit-card')
                        ->color('success')
                        ->modalWidth(MaxWidth::TwoExtraLarge)
                        ->form([
                                
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

    public static function getNavigationLabel(): string
    {
        return 'Процедуры'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Процедуры'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Процедуры'; // Rus tilidagi ko'plik shakli
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
            'view' => Pages\ViewAssignedProcedure::route('/{record}'),
        ];
    }
}

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
use App\Models\Patient;
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
                            ->label('История болезно')
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
                                                            $patientId = $get('../../patient_id'); // yoki `request()->get('patient_id')`
                                                                if (!$patientId || !$state) {
                                                                    $set('price', 0);
                                                                    return;
                                                                }

                                                                $isForeign = Patient::find($patientId)?->is_foreign;

                                                                $procedure = Procedure::find($state);
                                                                $price = $isForeign == 1 ? $procedure->price_foreign : $procedure->price_per_day;

                                                                $set('price', $price ?? 0);
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
            'view' => Pages\ViewAssignedProcedure::route('/{record}'),
        ];
    }
}

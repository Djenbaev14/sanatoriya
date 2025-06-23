<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalInspectionResource\Pages;
use App\Filament\Resources\MedicalInspectionResource\RelationManagers;
use App\Models\Inspection;
use App\Models\MedicalInspection;
use App\Models\Patient;
use App\Models\PaymentType;
use Filament\Forms;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class MedicalInspectionResource extends Resource
{
    protected static ?string $model = MedicalInspection::class;

    protected static ?string $navigationGroup = 'Касса';
    protected static ?int $navigationSort = 3;
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
                        Hidden::make('initial_doctor_id')
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
                            ->label('История болезно')
                            ->required()
                            ->options(function (Get $get) {
                                $patientId = $get('patient_id');

                                return \App\Models\MedicalHistory::where('patient_id', $patientId)
                                    // ->doesntHave('medicalInspection') // agar faqat bog‘lanmaganlar kerak bo‘lsa
                                    ->get()
                                    ->mapWithKeys(function ($history) {
                                        $formattedId = str_pad('№'.$history->id, 10);
                                        $formattedDate = \Carbon\Carbon::parse($history->created_at)->format('d.m.Y H:i');
                                        return [$history->id => $formattedId . ' - ' . $formattedDate];
                                    });
                            })
                            ->required()
                            ->columnSpan(6),
                        Select::make('assigned_doctor_id')
                            ->label('Врач')
                            ->options(function (Get $get) {
                                return \App\Models\User::whereHas('roles', function (Builder $query)  {
                                    $query->where('name', 'Доктор');
                                })->pluck('name', 'id');
                            })
                            ->required()
                            ->columnSpan(6),
                        Textarea::make('admission_diagnosis')
                            ->label('Диагноз')
                            ->rows(3)
                            ->columnSpan(12),
                        Textarea::make('complaints')
                            ->label('Жалобы')
                            ->rows(3)
                            ->columnSpan(12),
                        Textarea::make('medical_history')
                            ->label('Анамнез')
                            ->rows(3)
                            ->columnSpan(12),
                        Textarea::make('objectively')
                            ->label('Объективно')
                            ->rows(3)
                            ->columnSpan(12),
                        Textarea::make('treatment')
                            ->label('Лечение')
                            ->rows(3)
                            ->columnSpan(12),
                    ])->columns(12)->columnSpan(12)
            ]);
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return false;
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
                                        ->minValue(0.01)
                                        ->step(0.01)
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
                            try {
                                
                                // To'lovni saqlash
                                \App\Models\Payment::create([
                                    'patient_id' => $record->patient_id,
                                    'medical_inspection_id' => $record->id,
                                    'amount' => $data['amount'],
                                    'payment_type_id' => $data['payment_type_id'],
                                    'description' => $data['description'] ?? null,
                                ]);

                                if($record->getTotalCost() == $record->getTotalPaidAmount()){
                                    MedicalInspection::where('id',$record->id)->update([
                                        'status_payment_id'=>2
                                    ]);
                                }

                                // Muvaffaqiyat xabari
                                Notification::make()
                                    ->title('Выплата успешно добавлена!')
                                    ->success()
                                    ->body("Оплата: " . number_format($data['amount'], 2) . " сум")
                                ->send();
                            } catch (\Throwable $th) {
                                //throw $th;
                            }
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
            'index' => Pages\ListMedicalInspections::route('/'),
            'create' => Pages\CreateMedicalInspection::route('/create'),
            'edit' => Pages\EditMedicalInspection::route('/{record}/edit'),
            'view' => Pages\ViewMedicalInspection::route('/{record}'),
        ];
    }
}

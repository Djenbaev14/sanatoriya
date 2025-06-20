<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KassaProcedureResource\Pages;
use App\Filament\Resources\KassaProcedureResource\RelationManagers;
use App\Models\AssignedProcedure;
use App\Models\KassaProcedure;
use App\Models\PaymentType;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KassaProcedureResource extends Resource
{
    protected static ?string $model = AssignedProcedure::class;

    protected static ?string $navigationGroup = 'Касса';
    protected static ?int $navigationSort = 1;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status_payment_id',2)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status_payment_id', 2); // faqat status 1 bo'lganlar
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('patient.full_name')->label('ФИО')->searchable()->sortable(),
                TextColumn::make('total_paid')
                    ->label('Обшый сумма')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return number_format($record->getTotalCost(),0,'.',' ').' сум';
                    }),
                TextColumn::make('total_amount')
                    ->label('Одобрено')
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
                        return number_format($remaining, 0, '.', ' ') . ' сум';
                    }),
                TextColumn::make('created_at')->searchable()->label('Дата')->sortable(),
            ])
            ->defaultPaginationPageOption(50)
            ->actions([
                Action::make('add_payment')
                        ->label('Оплата')
                        ->icon('heroicon-o-credit-card')
                        ->color('success')
                        ->modalWidth(MaxWidth::TwoExtraLarge)
                        ->form([
                            // Section::make('Данные платежа')
                            //     ->schema([
                            //         Grid::make(2)
                            //             ->schema([
                            //                 TextInput::make('total_cost')
                            //                     ->label('Общие')
                            //                     ->disabled()
                            //                     ->default(function ($record) {
                            //                         return number_format($record->getTotalCost(), 0, '.', ' ') . ' сум';
                            //                     }),
                                                
                            //                 TextInput::make('total_paid')
                            //                     ->label('Оплачено')
                            //                     ->disabled()
                            //                     ->default(function ($record) {
                            //                         return number_format($record->getTotalPaidAmount(), 0, '.', ' ') . ' сум';
                            //                     }),
                            //             ]),
                                        
                            //         TextInput::make('remaining')
                            //             ->label('Остаток')
                            //             ->disabled()
                            //             ->default(function ($record) {
                            //                 $remaining = $record->getTotalCost() - $record->getTotalPaidAmount();
                            //                 return number_format($remaining, 0, '.', ' ') . ' сум';
                            //             }),
                            //     ]),
                                
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
                     Action::make('return_status')
                        ->label('Вы уверены?')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('danger')
                        ->modalWidth(MaxWidth::TwoExtraLarge)
                        ->modalDescription('Отправить данные в кассу для оплаты?')
                        ->modalSubmitActionLabel('Да, отправить')
                        ->action(function (array $data, $record) {
                        
                        // Kassaga yuborish logikasi
                        $record->update([
                            'status_payment_id' => '1',
                        ]);

                        Notification::make()
                            ->title('Запись успешно удалена')
                            ->success()
                            ->send();

                    }),
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
            'index' => Pages\ListKassaProcedures::route('/'),
            'create' => Pages\CreateKassaProcedure::route('/create'),
            'edit' => Pages\EditKassaProcedure::route('/{record}/edit'),
        ];
    }
}

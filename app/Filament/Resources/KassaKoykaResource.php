<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KassaKoykaResource\Pages;
use App\Filament\Resources\KassaKoykaResource\RelationManagers;
use App\Models\Accommodation;
use App\Models\KassaKoyka;
use App\Models\MedicalHistory;
use App\Models\PaymentType;
use Filament\Forms;
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

class KassaKoykaResource extends Resource
{
    protected static ?string $model = Accommodation::class;

    protected static ?string $navigationGroup = 'Касса';
    protected static ?int $navigationSort = 4;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('main_accommodation_id', null)->where('status_payment_id',2)->count();
    }
    
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('main_accommodation_id', null) // faqat status 1 bo'lganlar
            ->where('status_payment_id', 2); // faqat status 1 bo'lganlar
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('medicalHistory.number')->label('Ид')->searchable()->sortable(),
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
                        $remaining = $record->getTotalPaid();
                        return number_format($remaining, 0, '.', ' ') . ' сум';
                    }),
                TextColumn::make('total_debt')
                    ->label('Долг')
                    ->color('danger')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $remaining = $record->getTotalCost() - $record->getTotalPaidAndReturned();
                        $remaining = max(0, $remaining); // agar minus bo‘lsa 0 bo‘ladi
                        return number_format($remaining, 0, '.', thousands_separator: ' ') . ' сум';
                    }),
                TextColumn::make('advance_payment')
                    ->label('Возврат')
                    ->color('success')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $remaining = $record->getTotalReturned() ;
                        $remaining = max(0, $remaining); // agar minus bo‘lsa 0 bo‘ladi
                        return number_format($remaining, 0, '.', thousands_separator: ' ') . ' сум';
                    }),
                    
                TextColumn::make('returning_balance')
                    ->label('Остаток к возврату')
                    ->color('danger')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $remaining = $record->getTotalPaidAndReturned()  - $record->getTotalCost();
                        $remaining = max(0, $remaining); // agar minus bo‘lsa 0 bo‘ladi
                        return number_format($remaining, 0, '.', thousands_separator: ' ') . ' сум';
                    }),
                TextColumn::make('created_at')->searchable()->label('Дата')->sortable(),
            ])
            ->defaultPaginationPageOption(50)
            ->actions([
                Action::make('add_payment')
                        ->label('Оплата')
                        ->icon('heroicon-o-credit-card')
                        ->color('success')
                        ->visible(function ($record) {
                            return $record->getTotalCost() > $record->getTotalPaidAndReturned();
                        })
                        ->modalDescription(function ($record) {
                            $overpaid = $record->getTotalCost() - $record->getTotalPaidAndReturned();
                            return 'Сумма: ' . number_format($overpaid, 0, '.', ' ') . ' сум';
                        })
                        ->modalWidth(MaxWidth::TwoExtraLarge)
                        ->form([
                            Section::make('')
                                ->schema([
                                    TextInput::make('amount')
                                        ->label('Сумма')
                                        ->numeric()
                                        ->required()
                                        ->suffix('сум')
                                        ->placeholder('0.00')
                                        ->live()
                                        ->default(function ($record) {
                                            return $record->getTotalCost() - $record->getTotalPaidAndReturned();
                                        })
                                        ->afterStateUpdated(function ($state, $set, $record): void {
                                            $remaining = $record->getTotalCost() - $record->getTotalPaidAndReturned();
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
                                'accommodation_id' => $record->id,
                                'amount' => $data['amount'],
                                'payment_type_id' => $data['payment_type_id'],
                                'description' => $data['description'] ?? null,
                            ]);
                            if ($record->getTotalPaidAndReturned() == $record->getTotalCost()) {
                                    $record->update(['status_payment_id' => 3]); // 1 - to'langan
                                    $record->partner?->update(['status_payment_id' => 3]); // agar hamkor bo'lsa, u ham to'langan deb belgilanadi
                            }

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
                    //  Action::make('return_status')
                    //     ->label('Вы уверены?')
                    //     ->icon('heroicon-o-arrow-uturn-left')
                    //     ->color('danger')
                    //     ->modalWidth(MaxWidth::TwoExtraLarge)
                    //     ->modalDescription('Отправить данные в кассу для оплаты?')
                    //     ->visible(fn ($record) => $record->payments()->count() == 0)
                    //     ->modalSubmitActionLabel('Да, отправить')
                    //     ->action(function (array $data, $record) {
                        
                    //     // Kassaga yuborish logikasi
                    //     $record->update([
                    //         'status_payment_id' => '1',
                    //     ]);

                    //     Notification::make()
                    //         ->title('Запись успешно удалена')
                    //         ->success()
                    //         ->send();

                    // }),
                Action::make('return_overpayment')
                    ->label('Возврат')
                    ->icon('heroicon-o-banknotes')
                    ->color('danger')
                    ->visible(function ($record) {
                        return $record->getTotalPaidAndReturned() > $record->getTotalCost();
                    })
                    ->modalHeading('Возврат средств')
                    ->modalDescription(function ($record) {
                        $overpaid = $record->getTotalPaidAndReturned() - $record->getTotalCost();
                        return 'Сумма возврата: ' . number_format($overpaid, 0, '.', ' ') . ' сум';
                    })
                    ->form([
                        Section::make('')
                            ->schema([
                                TextInput::make('amount')
                                    ->label('Сумма возврата')
                                    ->numeric()
                                    ->required()
                                    ->suffix('сум')
                                    ->default(function ($record) {
                                        return $record->getTotalPaidAndReturned() - $record->getTotalCost();
                                    })
                                    ->maxValue(fn ($record) => $record->getTotalPaidAndReturned() - $record->getTotalCost()),
                                    
                                Select::make('payment_type_id')
                                    ->label('Тип оплаты')
                                    ->options(PaymentType::all()->pluck('name', 'id'))
                                    ->required(),
                                Textarea::make('description')
                                    ->label('Комментарий')
                                    ->rows(3),
                            ])
                    ])
                    ->action(function (array $data, $record) {
                        // Kiritilgan summani "minus" to‘lov sifatida yozamiz
                        \App\Models\Payment::create([
                            'patient_id' => $record->patient_id,
                            'accommodation_id' => $record->id,
                            'amount' => -1 * abs($data['amount']), // minus yoziladi
                            'payment_type_id' => $data['payment_type_id'],
                            'description' => $data['description'] ?? 'Возврат средств',
                        ]);
                        
                        if ($record->getTotalPaidAndReturned() == $record->getTotalCost()) {
                                $record->update(['status_payment_id' => 3]); // 1 - to'langan
                        }

                        Notification::make()
                            ->title('Сумма успешно возвращена!')
                            ->success()
                            ->body("Возврат: " . number_format($data['amount'], 0, '.', ' ') . " сум")
                            ->send();
                    })
                    ->modalSubmitActionLabel('Подтвердить')
                    ->modalCancelActionLabel('Отмена'),

            ])
            ->filters([
                //
            ]);
    }
    public static function getNavigationLabel(): string
    {
        return 'Для койка и питание'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Для койка и питание'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Для койка и питание'; // Rus tilidagi ko'plik shakli
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
            'index' => Pages\ListKassaKoykas::route('/'),
            'create' => Pages\CreateKassaKoyka::route('/create'),
            'edit' => Pages\EditKassaKoyka::route('/{record}/edit'),
        ];
    }
}

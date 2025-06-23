<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KassaLabTestResource\Pages;
use App\Filament\Resources\KassaLabTestResource\RelationManagers;
use App\Models\KassaLabTest;
use App\Models\LabTestHistory;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\User;
use Filament\Facades\Filament;
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

class KassaLabTestResource extends Resource
{
    
    protected static ?string $model = LabTestHistory::class;

    protected static ?string $navigationGroup = 'Касса';
    protected static ?int $navigationSort = 2;
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
                                $payment = Payment::create([
                                    'amount' => $data['amount'],
                                    'payment_type_id' => $data['payment_type_id'],
                                    'description' => $data['description'],
                                    'user_id' => Filament::auth()->id(),
                                    'lab_test_history_id' => $record->id,
                                'patient_id' => $record->patient_id,
                                ]);
                                // agar barcha to'lovlar amalga oshirilgan bo'lsa, statusni yangilash
                                if ($record->getTotalPaidAmount() == $record->getTotalCost()) {
                                    $record->update(['status_payment_id' => 3]); // 1 - to'langan
                                }
                                

                            // Muvaffaqiyat xabari
                            Notification::make()
                                ->title('Выплата успешно добавлена!')
                                ->success()
                                ->body("Оплата: " . number_format($data['amount'], 2) . " сум")
                                ->send();

                                // Update the total paid amount in the lab test history
                                $record->updateTotalPaidAmount();

                                Notification::make()
                                    ->title('Оплата успешно добавлена')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Ошибка при добавлении оплаты: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKassaLabTests::route('/'),
            'create' => Pages\CreateKassaLabTest::route('/create'),
            'edit' => Pages\EditKassaLabTest::route('/{record}/edit'),
        ];
    }
}

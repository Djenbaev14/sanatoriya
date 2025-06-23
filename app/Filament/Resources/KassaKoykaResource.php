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
        return static::getModel()::where('status_payment_id',2)->count();
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
                TextColumn::make('patient.full_name')->label('ФИО')->searchable()->sortable(),
                TextColumn::make('total_paid')
                    ->label('Обшый сумма')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return number_format($record->getBedAndMealCost(),0,'.',' ').' сум';
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
                        $remaining = $record->getBedAndMealCost() - $record->getTotalPaid();
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
                                        ->afterStateUpdated(function ($state, $set, $record): void {
                                            $remaining = $record->getBedAndMealCost() - $record->getTotalPaid();
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
                                'medical_history_id' => $record->id,
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
                        ->visible(fn ($record) => $record->payments()->count() == 0)
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

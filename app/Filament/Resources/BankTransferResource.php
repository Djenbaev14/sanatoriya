<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankTransferResource\Pages;
use App\Filament\Resources\BankTransferResource\RelationManagers;
use App\Models\BankTransfer;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankTransferResource extends Resource
{
    protected static ?string $model = BankTransfer::class;
    public static function getNavigationGroup(): string
    {
        return 'Касса';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Сумма')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->columnSpan(12),
                        Forms\Components\Select::make('payment_type_id')
                            ->label('Способ оплаты')
                            ->required()
                            // options
                            ->options(\App\Models\PaymentType::where('id',1)->pluck('name', 'id'))
                            ->placeholder('Выберите способ оплаты')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state) {
                                    $paymentType = \App\Models\PaymentType::find($state);
                                    if ($paymentType) {
                                        $set('commission_percent', $paymentType->commission_percent);
                                    }
                                } else {
                                    $set('commission_percent', null);
                                }
                            })
                            ->columnSpan(12),
                        Forms\Components\TextInput::make('commission_percent')
                            ->label('Комиссия %')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->required()
                            ->columnSpan(12),
                        Forms\Components\DateTimePicker::make('transferred_at')
                            ->label('Дата перевода')
                            ->required()
                            ->default(now())
                            ->columnSpan(12),
                    ])->columnSpan(12)->columns()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->label('Оплата')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, '.', ' ') . ' сум')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('commission_percent')
                    ->label('Комиссия %')
                    ->getStateUsing(function ($record) {
                        $amount = $record->amount ?? 0;
                        $percent = $record->commission_percent ?? 0;
                        $net = $amount * $percent / 100;
                        return $record->commission_percent.'% </br> '.number_format($net, 0, '.', ' ') . ' сум';
                    })
                    ->html()
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_amount')
                    ->label('Поступление')
                    ->getStateUsing(function ($record) {
                        $amount = $record->amount ?? 0;
                        $percent = $record->commission_percent ?? 0;
                        $net = $amount - ($amount * $percent / 100);
                        return number_format($net, 0, '.', ' ') . ' сум';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('paymentType.name')
                    ->label('Способ оплаты')    
                    ->sortable(),
                Tables\Columns\TextColumn::make('transferred_at')
                    ->label('Дата перевода')
                    ->dateTime()
                    ->sortable(),
            ])->filters([
                //
            ])->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Перевод денег на счет')
                    ->modalWidth(MaxWidth::ThreeExtraLarge)
                    ->action(function(array $data) {
                        try {
                            BankTransfer::create([
                                'amount' => $data['amount'],
                                'commission_percent' => $data['commission_percent'],
                                'payment_type_id' => $data['payment_type_id'],
                                'transferred_at' => $data['transferred_at'],
                            ]);

                            Notification::make()
                                ->title('Поступление в банк успешно создано!')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Ошибка при создании поступления в банк: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('id','desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->can('остаток в кассе');
    }



    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getNavigationLabel(): string
    {
        return 'Поступления в банк'; // Rus tilidagi nom
    }
    
    public static function getModelLabel(): string
    {
        return 'поступления в банк'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'поступления в банк'; // Rus tilidagi ko'plik shakli
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankTransfers::route('/'),
            'edit' => Pages\EditBankTransfer::route('/{record}/edit'),
        ];
    }
}

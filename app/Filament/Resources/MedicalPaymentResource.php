<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalPaymentResource\Pages;
use App\Filament\Resources\MedicalPaymentResource\RelationManagers;
use App\Models\MedicalHistory;
use App\Models\MedicalPayment;
use App\Models\PaymentType;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
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

class MedicalPaymentResource extends Resource
{
    protected static ?string $model = MedicalHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }
    
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['assignedProcedure.procedureDetails', 'labTestHistory.labTestDetails', 'accommodation'])
            ->withDebt();
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('Номер')->searchable()->sortable(),
                TextColumn::make('patient.full_name')->label('ФИО')->searchable()->sortable(),
                TextColumn::make('total_cost')
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
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->actions([
                Action::make('add_payment')
                        ->label('Оплата')
                        ->icon('heroicon-o-credit-card')
                        ->color('success')
                        ->modalWidth(MaxWidth::TwoExtraLarge)
                        ->visible(function ($record) {
                            return $record->getTotalCost() > $record->getTotalPaidAndReturned();
                        })
                        ->modalDescription(function ($record) {
                            $overpaid = $record->getTotalCost() - $record->getTotalPaidAndReturned();
                            return 'Сумма: ' . number_format($overpaid, 0, '.', ' ') . ' сум';
                        })
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
                                        ->default(function ($record) {
                                            return $record->getTotalCost() - $record->getTotalPaidAndReturned();
                                        })
                                        ->afterStateUpdated(function ($state, $set, $record) {
                                            $remaining = $record->getTotalCost() - $record->getTotalPaidAndReturned();
                                            if ($state > $remaining) {
                                                $set('amount', $remaining);
                                            }
                                        }),
                                    Select::make('payment_type_id')
                                        ->label('Тип оплаты')
                                        ->options(PaymentType::all()->pluck('name', 'id'))
                                        ->required()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            $paymentType = \App\Models\PaymentType::find($state);
                                            $set('is_submitted_to_bank', $paymentType && $paymentType->name === 'Терминал');
                                        }),
                                    Hidden::make('is_submitted_to_bank')
                                        ->dehydrated(true),
                                        
                                    Textarea::make('description')
                                        ->label('Izoh')
                                        ->placeholder('Коммент')
                                        ->maxLength(255)
                                        ->rows(3),
                                ]),
                        ])
                        ->action(function (array $data, $record) {
                            try {
                                $record->payments()->create([
                                    'patient_id' => $record->patient_id,
                                    'amount' => $data['amount'],
                                    'payment_type_id' => $data['payment_type_id'],
                                    'is_submitted_to_bank' => $data['is_submitted_to_bank'] ?? false,
                                    'description' => $data['description'],
                                    'user_id' => Filament::auth()->id(),
                                    'medical_history_id' => $record->id,
                                ]);
                                
                                if ($record->getTotalPaidAndReturned() == $record->getTotalCost()) {
                                        $record->assignedProcedure->update(['status_payment_id' => 3]); 
                                        $record->accommodation->update(['status_payment_id' => 3]); 
                                        $record->labTestHistory->update(['status_payment_id' => 3]); 
                                }

                                Notification::make()
                                    ->title('Оплата успешно добавлена')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Ошибка при добавлении оплаты')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->modalHeading('Оплата')
                        ->modalSubmitActionLabel('Сохранить')
                        ->modalCancelActionLabel('Отмена'),
            ]);
    }
    
    public static function getNavigationLabel(): string
    {
        return 'Журнал оплат'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Журнал оплат'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Журнал оплат'; // Rus tilidagi ko'plik shakli
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('касса');
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
            'index' => Pages\ListMedicalPayments::route('/'),
            'create' => Pages\CreateMedicalPayment::route('/create'),
            'view' => Pages\ViewMedicalPayment::route('/{record}'),
        ];
    }
}

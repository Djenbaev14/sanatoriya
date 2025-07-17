<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentProcedureResource\Pages;
use App\Filament\Resources\PaymentProcedureResource\RelationManagers;
use App\Models\AssignedProcedure;
use App\Models\PaymentProcedure;
use App\Models\ProcedureDetail;
use App\Models\ProcedurePayment;
use App\Models\ProcedurePaymentDetail;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentProcedureResource extends Resource
{
    protected static ?string $model = AssignedProcedure::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
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
                TextColumn::make('patient.full_name')->label('Bemor'),
                TextColumn::make('total_cost')
                ->formatStateUsing(function ($record) {
                    return $record->total_cost;
                })
                ->label('Umumiy'),
                TextColumn::make('total_paid')
                ->formatStateUsing(function ($record) {
                    return $record->total_paid;
                })
                ->label('To‘langan'),
                TextColumn::make('debt')
                ->formatStateUsing(function ($record) {
                    return $record->debt;
                })
                ->label('Qarzdorlik')->color('danger'),
            ])
            ->defaultPaginationPageOption(50)
            ->defaultSort('id','desc')
            ->filters([
                //
            ])
            ->actions([
                Action::make('оплата')
                    ->label('Оплата')
                    ->icon('heroicon-o-currency-dollar')
                    ->visible(fn ($record) => $record->debt > 0)
                    ->form(function (AssignedProcedure $record) {
                                return [
                                    Repeater::make('payment_items')
                                        ->addable(false)
                                        ->deletable(false)
                                        ->label('')
                                        ->default(function () use ($record) {
                                            return $record->ProcedureDetails
                                                ->map(function ($detail) {
                                                    return [
                                                        'procedure_id' => $detail->procedure->id,
                                                        'procedure_name' => $detail->procedure->name,
                                                        'price' => $detail->price,
                                                        'sessions' => $detail->sessions,
                                                    ];
                                                })->values()->all();
                                        })
                                        ->schema([
                                            Grid::make(5)->schema([
                                                TextInput::make('procedure_name')
                                                    ->label('Процедура')
                                                    ->disabled()
                                                    ->columnSpan(2),

                                                TextInput::make('price')
                                                    ->label('Цена')
                                                    ->numeric()
                                                    ->readOnly()
                                                    ->columnSpan(1),
                                                TextInput::make('sessions')
                                                    ->label('sessions')
                                                    ->readOnly()
                                                    ->columnSpan(1),
                                                Toggle::make('selected')
                                                    ->label('')
                                                    ->inline(true) // yonma-yon bo‘ladi
                                                    ->columnSpan(1)
                                                    ->reactive(),
                                                Hidden::make('procedure_id'),

                                            ]),
                                        ])
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            // umumiy narxni hisoblash
                                            $total = collect($state)
                                                ->filter(fn ($item) => $item['selected'] ?? false)
                                                ->sum(fn ($item) => $item['price'] * $item['sessions'] );

                                            $set('total_amount', $total);
                                        })
                                        ->columns(1),
                                    TextInput::make('total_amount')
                                        ->label('Сумма')
                                        ->disabled()
                                        ->numeric()
                                        ->reactive()
                                        ->afterStateHydrated(function ($set, $get) {
                                            $total = collect($get('payment_items'))
                                                ->filter(fn ($item) => $item['selected'] ?? false)
                                                ->sum(fn ($item) => $item['price'] * $item['sessions']);
                                            $set('total_amount', $total);
                                        }),

                                ];
                    })
                    ->action(function (array $data, $record) {
                        try {
                            $payment = ProcedurePayment::create([
                                'patient_id' => $record->patient_id,
                                'medical_history_id' => $record->medical_history_id,
                                'assigned_procedure_id' => $record->id,
                            ]);

                            foreach ($data['payment_items'] as $item) {
                                if($item['selected']){
                                    ProcedurePaymentDetail::create([
                                        'procedure_payment_id' => $payment->id,
                                        'assigned_procedure_id' => $record->id,
                                        'procedure_id' => $item['procedure_id'],
                                        'price' => $item['price'],
                                        'sessions' => $item['sessions'],
                                    ]);
                                }
                            }
                        } catch (\Throwable $th) {
                            throw $th;
                        }
                    })
                    ->slideOver()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getNavigationLabel(): string
    {
        return 'Для Процедуры'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Для Процедуры'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Для Процедуры'; // Rus tilidagi ko'plik shakli
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
            'index' => Pages\ListPaymentProcedures::route('/'),
            'create' => Pages\CreatePaymentProcedure::route('/create'),
            'edit' => Pages\EditPaymentProcedure::route('/{record}/edit'),
        ];
    }
}

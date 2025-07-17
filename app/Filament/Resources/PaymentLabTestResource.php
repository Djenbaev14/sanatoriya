<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentLabTestResource\Pages;
use App\Filament\Resources\PaymentLabTestResource\RelationManagers;
use App\Models\LabTestHistory;
use App\Models\LabTestPayment;
use App\Models\LabTestPaymentDetail;
use App\Models\PaymentLabTest;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class PaymentLabTestResource extends Resource
{
    protected static ?string $model = LabTestHistory::class;

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
                    ->form(function (LabTestHistory $record) {
                                return [
                                    Repeater::make('payment_items')
                                        ->addable(false)
                                        ->deletable(false)
                                        ->label('')
                                        ->default(function () use ($record) {
                                            return $record->labTestDetails
                                                ->map(function ($detail) {
                                                    return [
                                                        'lab_test_id' => $detail->lab_test->id,
                                                        'lab_test_name' => $detail->lab_test->name,
                                                        'price' => $detail->price,
                                                    ];
                                                })->values()->all();
                                        })
                                        ->schema([
                                            Grid::make(4)->schema([
                                                TextInput::make('lab_test_name')
                                                    ->label('Анализ')
                                                    ->disabled()
                                                    ->columnSpan(2),

                                                TextInput::make('price')
                                                    ->label('Цена')
                                                    ->numeric()
                                                    ->readOnly()
                                                    ->columnSpan(1),
                                                Toggle::make('selected')
                                                    ->label('')
                                                    ->inline(true) // yonma-yon bo‘ladi
                                                    ->columnSpan(1)
                                                    ->reactive(),
                                                Hidden::make('lab_test_id'),

                                            ]),
                                        ])
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            // umumiy narxni hisoblash
                                            $total = collect($state)
                                                ->filter(fn ($item) => $item['selected'] ?? false)
                                                ->sum(fn ($item) => $item['price'] );

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
                                                ->sum(fn ($item) => $item['price']);
                                            $set('total_amount', $total);
                                        }),

                                ];
                    })
                    ->action(function (array $data, $record) {
                        try {
                            $payment = LabTestPayment::create([
                            'patient_id' => $record->patient_id,
                            'medical_history_id' => $record->medical_history_id,
                            'lab_test_history_id' => $record->id,
                            ]);

                            foreach ($data['payment_items'] as $item) {
                                if($item['selected']){
                                    LabTestPaymentDetail::create([
                                    'lab_test_payment_id' => $payment->id,
                                    'lab_test_history_id' => $record->id,
                                    'lab_test_id' => $item['lab_test_id'],
                                    'price' => $item['price'],
                                    'sessions' => 1,
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
        return 'Для Анализ'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Для Анализ'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Для Анализ'; // Rus tilidagi ko'plik shakli
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
            'index' => Pages\ListPaymentLabTests::route('/'),
            'create' => Pages\CreatePaymentLabTest::route('/create'),
            'edit' => Pages\EditPaymentLabTest::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubmittedPaymentsResource\Pages;
use App\Filament\Resources\SubmittedPaymentsResource\RelationManagers;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\SubmittedPayments;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubmittedPaymentsResource extends Resource
{
    protected static ?string $model = Payment::class;

    // protected static ?string $navigationIcon = 'heroicon-o-building-library';
    public static function getNavigationGroup(): string
    {
        return 'Касса';
    }
    public static function getNavigationLabel(): string
    {
        return 'Сдано в банк'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Сдано в банк'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Сдано в банк'; // Rus tilidagi ko'plik shakli
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
            ->query(
                Payment::query()
                    ->where('is_submitted_to_bank', 1)
            )
            ->columns([
                TextColumn::make('payment_reason')
                    ->label('За что оплачено'),

                TextColumn::make('paymentType.name')
                    ->label('Тип оплаты'),
                    
                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('UZS')
                    ->summarize(Sum::make()->label('Общая сумма')),

                TextColumn::make('updated_at')
                    ->label('Дата обновления')
                    ->date('d.m.Y h:i'),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('for_type')
                    ->label('За что оплачено')
                    ->options([
                        'accommodation' => 'Палата (койка)',
                        'procedure'     => 'Лечение (процедура)',
                        'analysis'      => 'Анализ (лаборатория)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;
                        return $query
                            ->when($value === 'accommodation', fn($q) => $q->whereNotNull('accommodation_id'))
                            ->when($value === 'procedure',     fn($q) => $q->whereNotNull('assigned_procedure_id'))
                            ->when($value === 'analysis',      fn($q) => $q->whereNotNull('lab_test_history_id'));
                    }),
                SelectFilter::make('date_filter')
                    ->label('Дата')
                    ->options([
                        'today' => 'Сегодня',
                        'last_7_days' => 'Последние 7 дней',
                        'month' => 'С начала месяца',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;

                        return $query
                            ->when($value === 'today', fn ($q) => $q->whereDate('updated_at', today()))
                            ->when($value === 'last_7_days', fn ($q) => $q->whereDate('updated_at', '>=', now()->subDays(7)))
                            ->when($value === 'month', fn ($q) => $q->whereDate('updated_at', '>=', now()->startOfMonth()));
                    }),
                SelectFilter::make('payment_type_id')
                    ->label('Тип оплаты')
                    ->searchable()
                    ->options(fn () => 
                        PaymentType::all()->pluck('name', 'id')->map(fn ($name) =>$name))
                    ->preload(),
            ],layout: FiltersLayout::AboveContent);
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
            'index' => Pages\ListSubmittedPayments::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KassaBalanceResource\Pages;
use App\Filament\Resources\KassaBalanceResource\RelationManagers;
use App\Models\KassaBalance;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KassaBalanceResource extends Resource
{
    protected static ?string $model = Payment::class;

    // protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    public static function getNavigationGroup(): string
    {
        return 'Касса';
    }

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
                // TextColumn::make('patient.full_name')
                //     ->label('Больной'),
                    
                TextColumn::make('patient.full_name')
                    ->label('Больной'),

                TextColumn::make('payment_reason')
                    ->label('За что оплачено'),

                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('UZS')
                    ->summarize(Sum::make()->label('Общая сумма')),

                TextColumn::make('updated_at')
                    ->label('Дата обновления')
                    ->date('d.m.Y h:i'),
            ])
            ->filters([
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
                ],layout: FiltersLayout::AboveContent)
            ->persistFiltersInSession()    // 👈 Foydalanuvchi filtrlasa, u saqlanadi
            ->defaultPaginationPageOption(50)
            
            ->bulkActions([
                BulkAction::make('mark_as_submitted')
            ->label('Отметить как сданные в банк')
            ->icon('heroicon-m-banknotes')
            ->color('success')
            ->action(function ($records) {
                foreach ($records as $record) {
                        $record->update([
                            'is_submitted_to_bank' => true,
                        ]);
                }
            })
            // ->modalHeading('Подтверждение сдачи в банк')
            ->modalHeading(function ($records) {
                $sum = $records->sum('amount');
                return number_format($sum, 0, '.', ' ') . ' сум будут переданы в банк';
            })
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->modalSubmitActionLabel('Сдать в банк'),
                    ])
            ->defaultSort('created_at', 'desc');
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
        return 'Платежи'; // Rus tilidagi nom
    }
    
    public static function getModelLabel(): string
    {
        return 'Платежи'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Платежи'; // Rus tilidagi ko'plik shakli
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKassaBalances::route('/'),
            'create' => Pages\CreateKassaBalance::route('/create'),
            'edit' => Pages\EditKassaBalance::route('/{record}/edit'),
        ];
    }
}

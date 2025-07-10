<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KassaBalanceResource\Pages;
use App\Filament\Resources\KassaBalanceResource\RelationManagers;
use App\Models\KassaBalance;
use App\Models\Payment;
use App\Models\PaymentType;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

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
    
    // public static function shouldRegisterNavigation(): bool
    // {
    //     return false;
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('medicalHistory.number')->label('История номер')->searchable()->sortable(),
                TextColumn::make('patient.full_name')
                    ->label('Больной'),
                TextColumn::make('paymentType.name')
                    ->label('Тип платежа')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('UZS')
                    ->summarize(Sum::make()->label('Общая сумма')),

                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->date('d.m.Y h:i'),
            ])
            ->headerActions([
                ExportAction::make('export_excel')
                    ->label('Экспортировать в Excel')
                    ->exports([
                        ExcelExport::make()->fromTable()
                    ])
            ])
            ->filters([
                // select filter for payment type
                SelectFilter::make('payment_type_id')
                    ->label('Тип платежа')
                    ->options(PaymentType::query()->pluck('name', 'id'))
                    ->query(function (Builder $query, $data) {
                        return $query->when($data, fn ($q) =>
                            $q->where('payment_type_id', $data)
                        );
                    }),
                Filter::make('created_date_range')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('from')
                                    ->label('Первая дата')
                                    ->columnSpan(1),
                                DatePicker::make('until')
                                    ->label('Последняя дата')
                                    ->columnSpan(1),
                            ])
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['from'], fn ($q) =>
                            $q->whereDate('created_at', '>=', $data['from'])
                        )
                            ->when($data['until'], fn ($q) =>
                                $q->whereHas('accommodation', fn ($q) =>
                                    $q->whereDate('created_at', '<=', $data['until'])
                                )
                            );
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

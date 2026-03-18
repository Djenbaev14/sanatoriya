<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KassaBalanceResource\Pages;
use App\Filament\Resources\KassaBalanceResource\RelationManagers;
use App\Models\KassaBalance;
use App\Models\MedicalHistory;
use App\Models\Payment;
use App\Models\PaymentType;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
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
use pxlrbt\FilamentExcel\Columns\Column;

class KassaBalanceResource extends Resource
{
    protected static ?string $model = Payment::class;

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
    

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('medicalHistory.number')->label('История номер')->searchable()->sortable(),
                TextColumn::make('patient.full_name')->searchable()
                    ->label('Больной'),
                TextColumn::make('paymentType.name')
                    ->label('Тип платежа')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_paid_amount')
                    ->label('Сумма')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return $record->getTotalPaidAmount();
                    }),

                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->sortable()
                    ->date('d.m.Y h:i'),
            ])
            ->headerActions([
                Action::make('total_amount_summary')
                    ->label(function ($livewire) {
                        $filtered = $livewire->getFilteredTableQuery()->get();
                        $total = $filtered->sum(fn ($item) => $item->getTotalPaidAmount());
                        return 'Общая выплаченная сумма: ' . number_format($total, 0, '.', ' ') . ' сум';
                    })
                    ->disabled()
                    ->color('gray'),
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
                    ->query(fn (Builder $query, array $data) => 
                        $query->when($data['value'] ?? null, fn ($q, $value) =>
                            $q->where('payment_type_id', $value)
                        )
                    )->columnSpan(1),
                Filter::make('created_date_range')
                    ->form([
                        Grid::make(4)
                            ->schema([
                                DatePicker::make('from')
                                    ->label('Первая дата')
                                    ->columnSpan(2),
                                DatePicker::make('until')
                                    ->label('Последняя дата')
                                    ->columnSpan(2),
                            ])
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['from'], fn ($q) =>
                            $q->whereDate('created_at', '>=', $data['from'])
                        )
                            ->when($data['until'], fn ($q) =>
                                    $q->whereDate('created_at', '<=', $data['until'])
                            );
                    }),
                ],layout: FiltersLayout::AboveContent)
            ->persistFiltersInSession()    // 👈 Foydalanuvchi filtrlasa, u saqlanadi
            ->defaultPaginationPageOption(50)
            ->actions([
                Action::make('check')
                    ->url(fn ($record) => route('payment-log.view', ['record' => $record->id]))
                    ->openUrlInNewTab()
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
            'view' => Pages\ViewKassaBalance::route('/{record}'),
        ];
    }
}

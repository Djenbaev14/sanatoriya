<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FinancialReportResource\Pages;
use App\Filament\Resources\FinancialReportResource\RelationManagers;
use App\Models\FinancialReport;
use App\Models\MedicalHistory;
use App\Models\Payment;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class FinancialReportResource extends Resource
{
    protected static ?string $model = MedicalHistory::class;

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
                TextColumn::make('accommodation.admission_date')->label('Дата поступления')
                    ->formatStateUsing(function ($state) {
                        return $state ? \Carbon\Carbon::parse($state)->format('d-m-Y') : null;
                    })
                    ->sortable(),
                TextColumn::make('number')->label('Номер')->searchable()->sortable(),
                TextColumn::make('patient.full_name')->label('ФИО')
                    ->searchable()
                    ->limit(20)
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label('Сумма')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return $record->getTotalCost();
                    }),
                TextColumn::make('total_paid_sum')
                    ->label('Оплачено')
                    ->color('success')
                    ->badge(),
                TextColumn::make('remaining_debt')
                    ->label('Дебт')
                    ->color('danger')
                    ->badge(),
                TextColumn::make('total_ward_payment')
                    ->label('Койка')
                    ->badge(),
                TextColumn::make('total_meal_payment')
                    ->label('Питание')
                    ->badge(),
                TextColumn::make('total_ward_payment_partner')
                    ->label('Койка (Уход)')
                    ->badge(),
                TextColumn::make('total_meal_payment_partner')
                    ->label('Питание (Уход)')
                    ->badge(),
                TextColumn::make('total_medical_services_payment')
                    ->label('Мед услуг')
                    ->badge(),
            ])
            ->defaultPaginationPageOption(50)
            ->defaultSort('number','asc')
            ->headerActions([
                Action::make('total_amount_summary')
                    ->visible(fn () => request()->has('tableFilters.created_month_year.month') && request()->has('tableFilters.created_month_year.year'))
                    ->label(function ($livewire) {
                        $filtered = $livewire->getFilteredTableQuery()->get();
                        $total = $filtered->sum(fn ($item) => $item->getTotalCost());

                        return 'Общая сумма: ' . number_format($total, 0, '.', ' ') . ' сум';
                    })
                    ->disabled()
                    ->color('primary'),
                Action::make('current_month_payments')
                    ->visible(fn () => request()->has('tableFilters.created_month_year.month') && request()->has('tableFilters.created_month_year.year'))
                    ->label(function ($livewire) {
                        $month = request()->input('tableFilters.created_month_year.month');
                        $year = request()->input('tableFilters.created_month_year.year');

                        // Filtrlangan queryni olish
                        $filtered = Payment::whereYear('created_at', $year)
                            ->whereMonth('created_at', $month)
                            ->get();

                        // Har bir medical_history ichidagi paymentlardan umumiy summa
                        $total = $filtered->sum(fn ($item) =>
                            $item->sum(fn ($p) => $p->getTotalPaidAmount())
                        );
                        return 'Платежи текущего месяца: ' . number_format($total, 0, '.', ' ') . ' so‘m';
                    })
                    ->disabled()
                    ->color('success'),
                Action::make('carryover_balance')
                    ->visible(fn () => request()->has('tableFilters.created_month_year.month') && request()->has('tableFilters.created_month_year.year'))
                    ->label(function ($livewire) {
                        $month = request()->input('tableFilters.created_month_year.month');
                        $year = request()->input('tableFilters.created_month_year.year');

                        $filtered = $livewire->getFilteredTableQuery()->get();
                        $total = $filtered->sum(fn ($item) => $item->getTotalCost());

                        // Filtrlangan queryni olish
                        $filtered1 = Payment::whereYear('created_at', $year)
                            ->whereMonth('created_at', $month)
                            ->get();
                            

                        // Har bir medical_history ichidagi paymentlardan umumiy summa
                        $total1 = $filtered1->sum(fn ($item) =>
                            $item->sum(fn ($p) => $p->getTotalPaidAmount())
                        );
                        

                        return 'Переходящий остаток: ' . number_format($total-$total1, 0, '.', ' ') . ' сум';
                    })
                    ->disabled()
                    ->color('warning'),

                ExportAction::make('export_excel')
                    ->label('Экспортировать в Excel')
                    ->exports([
                         ExcelExport::make()->fromTable()
                        // ->modifyQueryUsing(function ($query, $livewire) {
                        //     return $livewire->getFilteredTableQuery()->orderBy('number','asc'); // Filtrlangan queryni qaytaradi
                        // })
                        //  ->withColumns([
                        //     Column::make('№') // tartib raqami
                        //         ->formatStateUsing(fn ($record, $loopIndex) => $loopIndex + 1), // 0-based bo'lsa +1 qilamiz

                        //     Column::make('number')
                        //         ->heading('Касаллик тарики ракмаи'),
                                
                        //     Column::make('patient.full_name')
                        //         ->heading('Ф.И.Ш'),
                        //     // Column::make('patient.district.name') // tartib raqami
                        //     //     ->heading('Яшаш манзили'),
                        //     // Column::make('patient.birth_date') // tartib raqami
                        //     //     ->heading('Тугилган йили'),
                        //     Column::make('accommodation.admission_date') // tartib raqami
                        //         ->formatStateUsing(function ($state) {
                        //             return $state ? \Carbon\Carbon::parse($state)->format('d-m-Y') : null;
                        //         })
                        //         ->heading('Келган вакти'),
                        //     Column::make('accommodation.discharge_date') // tartib raqami
                        //         ->formatStateUsing(function ($state) {
                        //             return $state ? \Carbon\Carbon::parse($state)->format('d-m-Y') : null;
                        //         })
                        //         ->heading('Чикган вакти'),
                        //     Column::make('total_cost') // tartib raqami
                        //         ->heading('Шартнома суммаси'),
                        //     // ✅ Yangi ustun — jami hisoblangan summa
                        //     Column::make('total_paid_sum')
                        //         ->heading('Жами тўланган сумма'),
                        //     Column::make('remaining_debt')
                        //         ->heading('Кариз суммаси'),
                        //     Column::make('total_ward_payment') // tartib raqami
                        //         ->heading('Койка учун туланган сумма'),
                        //     Column::make('total_meal_payment') // tartib raqami
                        //         ->heading('Питание'),
                        //     Column::make('total_medical_services_payment') // tartib raqami
                        //         ->heading('Мед услуг'),
                        //     Column::make('total_ward_payment_partner') // tartib raqami
                        //         ->heading('Койка (Уход)'),
                        //     Column::make('total_meal_payment_partner') // tartib raqami
                        //         ->heading('Питание (Уход)'),

                        // ])
                    ])
            ])
            ->filters([
                Filter::make('created_month_year')
                    ->form([
                        Grid::make()
                            ->columnSpan(6)
                            ->columns(6)
                            ->schema([
                                Select::make('month')
                                    ->label('Месяц')
                                    ->options([
                                        '01' => 'Январ',
                                        '02' => 'Феврал',
                                        '03' => 'Март',
                                        '04' => 'Апрел',
                                        '05' => 'Май',
                                        '06' => 'Июнь',
                                        '07' => 'Июль',
                                        '08' => 'Август',
                                        '09' => 'Сентябр',
                                        '10' => 'Октябр',
                                        '11' => 'Ноябр',
                                        '12' => 'Декабр',
                                    ])
                                    ->native(false)
                                    ->searchable()
                                    ->columnSpan(6),

                                Select::make('year')
                                    ->label('Год')
                                    ->options(
                                        collect(range(now()->year, 2025))
                                            ->mapWithKeys(fn ($year) => [$year => $year])
                                    )
                                    ->native(false)
                                    ->searchable()
                                    ->columnSpan(6),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                ($data['month'] ?? null) && ($data['year'] ?? null),
                                fn ($q) => $q->whereHas('accommodation', fn ($sub) =>
                                    $sub->whereYear('admission_date', $data['year'])
                                        ->whereMonth('admission_date', $data['month'])
                                )
                            )
                            ->when(
                                ($data['year'] ?? null) && !($data['month'] ?? null),
                                fn ($q) => $q->whereHas('accommodation', fn ($sub) =>
                                    $sub->whereYear('admission_date', $data['year'])
                                )
                            );
                    }),
            ], layout: FiltersLayout::AboveContent);

            
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
        return 'Финансовый отчёт'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'История болезни — финансовый отчёт'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'История болезни — финансовый отчёт'; // Rus tilidagi ko'plik shakli
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinancialReports::route('/'),
            'create' => Pages\CreateFinancialReport::route('/create'),
            'edit' => Pages\EditFinancialReport::route('/{record}/edit'),
        ];
    }
}

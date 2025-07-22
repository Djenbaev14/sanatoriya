<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FinancialReportResource\Pages;
use App\Filament\Resources\FinancialReportResource\RelationManagers;
use App\Models\FinancialReport;
use App\Models\MedicalHistory;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
                        return number_format($record->getTotalCost(),0,'.',' ').' сум';
                    }),
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
                ExportAction::make('export_excel')
                    ->label('Экспортировать в Excel')
                    ->exports([
                         ExcelExport::make()
                         ->withColumns([
                            Column::make('№') // tartib raqami
                                ->formatStateUsing(fn ($record, $loopIndex) => $loopIndex + 1), // 0-based bo'lsa +1 qilamiz

                            Column::make('number')
                                ->heading('Касаллик тарики ракмаи'),
                                
                            Column::make('patient.full_name')
                                ->heading('Ф.И.Ш'),
                            Column::make('patient.district.name') // tartib raqami
                                ->heading('Яшаш манзили'),
                            Column::make('patient.birth_date') // tartib raqami
                                ->heading('Тугилган йили'),
                            Column::make('accommodation.admission_date') // tartib raqami
                                ->formatStateUsing(function ($state) {
                                    return $state ? \Carbon\Carbon::parse($state)->format('d-m-Y') : null;
                                })
                                ->heading('Келган вакти'),
                            Column::make('accommodation.discharge_date') // tartib raqami
                                ->formatStateUsing(function ($state) {
                                    return $state ? \Carbon\Carbon::parse($state)->format('d-m-Y') : null;
                                })
                                ->heading('Чикган вакти'),
                            Column::make('accommodation.ward_day') // tartib raqami
                                ->heading('Бажарилган койка уринлар сони'),
                            Column::make('accommodation.admission_date') // tartib raqami
                                ->formatStateUsing(function ($state) {
                                    return $state ? \Carbon\Carbon::parse($state)->format('d-m-Y') : null;
                                })
                                ->heading('Шартнома санаси'),
                            Column::make('total_cost') // tartib raqami
                                ->heading('Шартнома суммаси'),
                            Column::make('total_ward_payment') // tartib raqami
                                ->heading('Койка учун туланган сумма'),
                            Column::make('total_meal_payment') // tartib raqami
                                ->heading('Овкатланиш харажати'),
                            Column::make('total_medical_services_payment') // tartib raqami
                                ->heading('даволаниш учун'),
                            Column::make('total_meal_payment_partner') // tartib raqami
                                ->heading('Койка (Уход)'),
                            Column::make('total_meal_payment') // tartib raqami
                                ->heading('Питание (Уход)'),


                        ]),
                    ])
            ])
            ->filters([
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
                        return $query
                            ->when($data['from'], fn ($q) =>
                                $q->whereHas('accommodation', fn ($query) =>
                                    $query->whereDate('admission_date', '>=', $data['from'])
                                )
                            )
                            ->when($data['until'], fn ($q) =>
                                $q->whereHas('accommodation', fn ($query) =>
                                    $query->whereDate('admission_date', '<=', $data['until'])
                                )
                            );
                    }),
                ],layout: FiltersLayout::AboveContent);
            
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

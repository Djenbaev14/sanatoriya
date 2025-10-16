<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompletedPhysiotherapyResource\Pages;
use App\Filament\Resources\CompletedPhysiotherapyResource\RelationManagers;
use App\Models\CompletedPhysiotherapy;
use App\Models\ProcedureSession;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompletedPhysiotherapyResource extends Resource
{
    protected static ?string $model = ProcedureSession::class;
    
    protected static ?string $navigationGroup = 'Физиотерапия';
    protected static ?int $navigationSort = 2;


    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()
            ->where('is_completed', true)
            ->whereHas('procedureDetail', function ($query) {
                $query->where('executor_id', auth()->id());
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('assignedProcedure.patient.full_name')->label('Пациент')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('procedure.name')->label('Процедура')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('session_date')->label('Дата сеанса')->date()->sortable(),
            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        DatePicker::make('date')
                            ->label('Дата')
                            ->default(Carbon::today()), // default bugungi sana
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date'], fn ($q, $date) => 
                                $q->whereDate('session_date', $date) // date_column o‘rniga o‘z ustuningizni yozing
                            );
                    }),
                    
                SelectFilter::make('procedure_id')
                    ->label('Процедура')
                    ->options(function () {
                        return \App\Models\Procedure::whereHas('details', function ($q) {
                            $q->where('executor_id', auth()->id());
                        })->pluck('name', 'id');
                    })
                    ->searchable()
                    ->multiple(),
            ],layout: FiltersLayout::AboveContent)
            ->actions([
            ])
            ->bulkActions([
            ]);
    }
    // public static function canAccess(): bool
    // {
    //     return auth()->user()?->can('view_any_procedure_session') ?? false;
    // }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::query()
            ->where('is_completed', true)
            ->whereHas('procedureDetail', function ($query) {
                $query->where('executor_id', auth()->id());
            })->count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Завершенные'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Завершенные'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Завершенные'; // Rus tilidagi ko'plik shakli
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompletedPhysiotherapies::route('/'),
            'create' => Pages\CreateCompletedPhysiotherapy::route('/create'),
            'edit' => Pages\EditCompletedPhysiotherapy::route('/{record}/edit'),
        ];
    }
}

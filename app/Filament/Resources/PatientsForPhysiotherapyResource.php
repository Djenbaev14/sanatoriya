<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientsForPhysiotherapyResource\Pages;
use App\Filament\Resources\PatientsForPhysiotherapyResource\RelationManagers;
use App\Models\PatientsForPhysiotherapy;
use App\Models\ProcedureSession;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatientsForPhysiotherapyResource extends Resource
{
    protected static ?string $model = ProcedureSession::class;
    protected static ?string $navigationGroup = 'Физиотерапия';
    protected static ?int $navigationSort = 1;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    
    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()
            ->where('is_completed', false)
            ->whereHas('procedureDetail', function ($query) {
                $query->where('executor_id', auth()->id());
            });
    }
    public static function table(Table $table): Table
    {
        return $table
            // ->headerActions([
            //     Action::make('todayCount')
            //         ->label(fn () => 'сегодня: ' . \App\Models\ProcedureSession::query()
            //             ->whereHas('procedureDetail', function ($query) {
            //                 $query->where('executor_id', auth()->id());
            //             })
            //             ->whereDate('session_date', today())
            //             ->where('is_completed', false)
            //             ->count())
            //         ->color('info')
            //         ->disabled(),

            //     Action::make('tomorrowCount')
            //         ->label(fn () => 'завтра: ' . \App\Models\ProcedureSession::query()
            //             ->whereHas('procedureDetail', function ($query) {
            //                 $query->where('executor_id', auth()->id());
            //             })
            //             ->whereDate('session_date', today()->addDay())
            //             ->where('is_completed', false)
            //             ->count())
            //         ->color('success')
            //         ->disabled(),

            //     Action::make('overdueCount')
            //         ->label(fn () => 'Просроченный: ' . \App\Models\ProcedureSession::query()
            //             ->whereHas('procedureDetail', function ($query) {
            //                 $query->where('executor_id', auth()->id());
            //             })
            //             ->whereDate('session_date', '<', today())
            //             ->where('is_completed', false)
            //             ->count())
            //         ->color('danger')
            //         ->disabled(),
            // ])
            ->columns([
                // patient full_name, procedure name, session_date, is_completed
                Tables\Columns\TextColumn::make('assignedProcedure.patient.full_name')->label('Пациент')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('procedure.name')->label('Процедура')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('session_date')->label('Дата сеанса')->date()->sortable(),
            ])
            ->actions([
                Action::make('complete')
                    ->label('Получить лечение')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->modalHeading('Подтвердить?')   // ✅ modal title
                    ->modalDescription('Вы хотите отметить этот сеанс как завершенный. Подтвердить?') // ✅ modal body
                    ->modalSubmitActionLabel('Да')       // ✅ confirm tugma
                    ->modalCancelActionLabel('Нет')      // ✅ cancel tugma
                    ->requiresConfirmation() // ✅ tasdiqlash chiqadi
                    ->visible(fn ($record) => ! $record->is_completed)
                    ->action(function ($record) {
                        if ($record->session_date > now()->toDateString()) {
                            Notification::make()
                                ->title('Ошибка!')
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->update([
                            'is_completed' => true,
                            'completed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Успешный!')
                            ->body('Сеанс успешно завершен.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('complete_all')
                    ->label('✅ Барчасини тасдиқлаш')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            if ($record->session_date != now()->toDateString()) {
                                continue;
                            }

                            $record->update([
                                'is_completed' => true,
                                'completed_at' => now(),
                            ]);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Успешно!')
                            ->body('Барча белгиланган сеанслар тасдиқланди.')
                            ->success()
                            ->send();
                    }),
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
            ],layout: FiltersLayout::AboveContent);
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::query()
            ->where('is_completed', false)
            ->whereHas('procedureDetail', function ($query) {
                $query->where('executor_id', auth()->id());
            })->count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_any_procedure_session') ?? false;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    
    public static function getNavigationLabel(): string
    {
        return 'Ожидание'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Ожидание'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Ожидание'; // Rus tilidagi ko'plik shakli
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatientsForPhysiotherapies::route('/'),
            'create' => Pages\CreatePatientsForPhysiotherapy::route('/create'),
            'edit' => Pages\EditPatientsForPhysiotherapy::route('/{record}/edit'),
        ];
    }
}

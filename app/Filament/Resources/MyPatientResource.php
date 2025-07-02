<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalHistoryResource\Pages\ViewMedicalHistory;
use App\Filament\Resources\MyPatientResource\Pages;
use App\Filament\Resources\MyPatientResource\RelationManagers;
use App\Models\MedicalHistory;
use App\Models\MyPatient;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MyPatientResource extends Resource
{
    protected static ?string $model = MedicalHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    public static function getNavigationBadge(): ?string
    {
        
        return static::getModel()::whereHas('medicalInspection', function (Builder $query): void {
            $query->where('assigned_doctor_id', auth()->id());
        })
        ->whereDoesntHave('departmentInspection') // departmentInspection mavjud emasligini tekshiradi
        ->count();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                MedicalHistory::query()
                    ->whereHas('medicalInspection', function (Builder $query) {
                        $query->where('assigned_doctor_id', auth()->user()->id);
                    })
            )
            ->columns([
                TextColumn::make('number')->label('Номер')->searchable()->sortable(),
                TextColumn::make('patient.full_name')->label('ФИО')->searchable()->sortable(),
                IconColumn::make('accommodation')
                    ->label('Условия размещения')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->accommodation))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('medicalInspection')
                    ->label('Приемный Осмотр')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->medicalInspection))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('departmentInspection')
                    ->label('Отделение Осмотр')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->departmentInspection))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('labTestHistory')
                    ->label('Анализы')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->labTestHistory))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('assignedProcedure')
                    ->label('Назначенные процедуры')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->assignedProcedure))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')->label('Дата')->dateTime()->sortable(),
                // TextColumn::make('accommodation.discharge_date')->label('Дата выписки')->dateTime()->sortable(),,
            ])
            ->defaultPaginationPageOption(50)
            ->defaultSort('number', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view_medical_history')
                    ->label('История болезни')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => MedicalHistoryResource::getUrl(
                        name: 'view',
                        parameters: ['record' => $record->id]
                    )),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('Доктор') ?? false;
    }
    public static function getNavigationLabel(): string
    {
        return 'Мои больные'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Мои больные'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Мои больные'; // Rus tilidagi ko'plik shakli
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
            'index' => Pages\ListMyPatients::route('/'),
            // 'edit' => Pages\EditMyPatient::route('/{record}/edit'),
        ];
    }
}

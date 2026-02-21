<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MySectionResource\Pages;
use App\Filament\Resources\MySectionResource\RelationManagers;
use App\Models\MySection;
use App\Models\Ward;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MySectionResource extends Resource
{
    protected static ?string $model = Ward::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['currentAccommodations.medicalHistory.medicalInspection'])
            ->whereHas('currentAccommodations.medicalHistory.medicalInspection', function (Builder $query) {
                $query->where('assigned_doctor_id', auth()->id());
            });
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
            
                TextColumn::make('current_patients_display')
                    ->label('Больные')
                    ->wrap()
                    ->html(),
            ])
            ->defaultPaginationPageOption(50);
            
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('Доктор') ?? false;
    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getNavigationLabel(): string
    {
        return 'Мой отделение'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Мой отделение'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Мой отделение'; // Rus tilidagi ko'plik shakli
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMySections::route('/'),
        ];
    }
}

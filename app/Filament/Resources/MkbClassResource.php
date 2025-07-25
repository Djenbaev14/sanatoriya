<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MkbClassResource\Pages;
use App\Filament\Resources\MkbClassResource\RelationManagers;
use App\Models\MkbClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MkbClassResource extends Resource
{
    protected static ?string $model = MkbClass::class;


    protected static ?string $navigationGroup = 'Настройка';
    protected static ?int $navigationSort = 6;

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
                TextColumn::make('name'),
                TextColumn::make('has_child'),
                TextColumn::make('node_cd'),
            ])
            ->filters([
                //
            ])
            ->actions([
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
        public static function canAccess(): bool
    {
        return auth()->user()?->can('настройки');
    }
    public static function getNavigationLabel(): string
    {
        return 'МКБ 10 клас'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'МКБ 10 клас'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'МКБ 10 клас'; // Rus tilidagi ko'plik shakli
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMkbClasses::route('/'),
            'create' => Pages\CreateMkbClass::route('/create'),
            'edit' => Pages\EditMkbClass::route('/{record}/edit'),
        ];
    }
}

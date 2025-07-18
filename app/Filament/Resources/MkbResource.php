<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MkbResource\Pages;
use App\Filament\Resources\MkbResource\RelationManagers;
use App\Models\Mkb;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MkbResource extends Resource
{
    protected static ?string $model = Mkb::class;

    protected static ?string $navigationGroup = 'Настройка';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mkb_code'),
                TextColumn::make('mkb_name'),
            ])
            ->defaultPaginationPageOption(50)
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
        return 'МКБ 10'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'МКБ 10'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'МКБ 10'; // Rus tilidagi ko'plik shakli
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMkbs::route('/'),
            'create' => Pages\CreateMkb::route('/create'),
            'edit' => Pages\EditMkb::route('/{record}/edit'),
        ];
    }
}

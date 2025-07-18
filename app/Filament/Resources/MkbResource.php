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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMkbs::route('/'),
            'create' => Pages\CreateMkb::route('/create'),
            'edit' => Pages\EditMkb::route('/{record}/edit'),
        ];
    }
}

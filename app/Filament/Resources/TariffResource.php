<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TariffResource\Pages;
use App\Filament\Resources\TariffResource\RelationManagers;
use App\Models\Tariff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TariffResource extends Resource
{
    protected static ?string $model = Tariff::class;

    protected static ?string $navigationGroup = 'Настройка';

    protected static ?int $navigationSort = 4;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('daily_price')
                    ->label('Цена')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('partner_daily_price')
                    ->label('Партнерская цена')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('foreign_daily_price')
                    ->label('Иностранная цена')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable(),
                Tables\Columns\TextColumn::make('daily_price')
                    ->label('Цена')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('partner_daily_price')
                    ->label('Партнерская цена')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('foreign_daily_price')
                    ->label('Иностранная цена')
                    ->numeric()
                    ->sortable(),
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
    
    public static function canAccess(): bool
    {
        return auth()->user()?->can('настройки');
    }
    public static function getNavigationLabel(): string
    {
        return 'Тариф койка'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Тариф койка'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Тариф койка'; // Rus tilidagi ko'plik shakli
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTariffs::route('/'),
            'create' => Pages\CreateTariff::route('/create'),
            'edit' => Pages\EditTariff::route('/{record}/edit'),
        ];
    }
}

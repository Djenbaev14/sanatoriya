<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationGroup = 'Склад';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('склад');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Название')
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->required()
                            ->label('Телефон')
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull()
                            ->maxLength(255),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->action(fn (array $data): Supplier => Supplier::create($data)),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('phone')->sortable()->searchable(),
            ])
            ->defaultSort('id','desc')
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modal()
                    ->slideOver()
                    ->modalHeading('Изменение')
                    ->modalWidth('lg')
                    ->modalAlignment('end')
                    ->using(fn (Supplier $record, array $data): Supplier => $record->update($data)),
            ]);
    }

    
    public static function getNavigationLabel(): string
    {
        return 'Поставщики'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Поставщики'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Поставщики'; // Rus tilidagi ko'plik shakli
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
            'index' => Pages\ListSuppliers::route('/'),
        ];
    }
}

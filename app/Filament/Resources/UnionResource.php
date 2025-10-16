<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnionResource\Pages;
use App\Filament\Resources\UnionResource\RelationManagers;
use App\Models\Union;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnionResource extends Resource
{
    protected static ?string $model = Union::class;
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
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Название')
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->action(fn (array $data): Union => Union::create($data)),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
            ])
            ->defaultSort('id','desc')
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modal()
                    ->slideOver()
                    ->modalHeading('Изменение')
                    ->modalWidth('lg')
                    ->modalAlignment('end')
                    ->using(fn (Union $record, array $data): Union => $record->update($data)),
            ]);
    }

    public static function getNavigationLabel(): string
    {
        return 'Единица измерения'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Единица измерения'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Единица измерения'; // Rus tilidagi ko'plik shakli
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
            'index' => Pages\ListUnions::route('/'),
        ];
    }
}

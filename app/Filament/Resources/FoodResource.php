<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FoodResource\Pages;
use App\Filament\Resources\FoodResource\RelationManagers;
use App\Models\Food;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FoodResource extends Resource
{
    protected static ?string $model = Food::class;

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
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название блюда')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(12),
                        Forms\Components\Repeater::make('foodProduct')
                            ->relationship('foodProduct')
                            ->label('Продукты блюда')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Продукт')
                                    ->relationship('product', 'name') // bu FoodProduct modelidagi "product()"
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select a product')
                                    ->columnSpan(6)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $product = \App\Models\Product::with('union')->find($state);
                                            $set('unit_name', $product?->union?->name ?? '');
                                        } else {
                                            $set('unit_name', '');
                                        }
                                    }),
                                Hidden::make('unit_name')->reactive(),

                                Forms\Components\TextInput::make('product_quantity')
                                    ->label('Количество продукта')
                                    ->minValue(0)
                                    ->suffix(fn (callable $get) => $get('unit_name')) // product tanlansa suffix sifatida chiqadi
                                    ->step(0.01)
                                    ->columnSpan(3),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Количество')
                                    ->required()
                                    ->suffix('На человека')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->columnSpan(3),
                            ])->columnSpan(12)->columns(12)
                    ])->columnSpan(12)
            ]);
    }

    
    public static function getNavigationLabel(): string
    {
        return 'Блюда'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Блюда'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Блюда'; // Rus tilidagi ko'plik shakli
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d.m.Y H:i')
                    ->label('Создано')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('id','desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListFood::route('/'),
        ];
    }
}

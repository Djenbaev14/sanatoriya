<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InspectionResource\Pages;
use App\Filament\Resources\InspectionResource\RelationManagers;
use App\Models\Inspection;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InspectionResource extends Resource
{
    protected static ?string $model = Inspection::class;

    protected static ?string $navigationGroup = 'Услуги';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                ->schema([
                    TextInput::make('name')
                        ->label('Название')
                        ->required()
                        ->maxLength(255)->columnSpan(12),
                    TextInput::make('price')
                        ->label('Цена')
                        ->required()
                        ->maxLength(255)->columnSpan(12),
                    TextInput::make('price_foreign')
                        ->label('Цена для иностр')
                        ->required()
                        ->maxLength(255)->columnSpan(12),
                ])->columns(12)->columnSpan(12)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            
            ->headerActions([
                CreateAction::make()
                    ->slideOver()
                    ->modalWidth(MaxWidth::Medium)
                    ->action(function (array $data) {
                            Inspection::create([
                                'name' => $data['name'],
                                'price' => $data['price'],
                                'price_foreign' => $data['price_foreign'],
                            ]);

                            Notification::make()
                                ->title('Осмотр табыслы жаратылды!')
                                ->success()
                                ->send();
                        }),
            ])
            ->defaultSort('id','desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('index')
                    ->label('№')
                    ->rowIndex(), // avtomatik tartib raqami
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->extraAttributes([
                        'class' => 'text-gray-500 dark:text-gray-300 text-xs'
                    ])
                    ->columnSpan(3),
                Tables\Columns\TextColumn::make('price')
                        ->label('Цена')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        return number_format($state, 0, '.', ' ') . " сум";  // Masalan, 1000.50 ni 1,000.50 formatida
                    })
                    ->extraAttributes([
                        'class' => 'text-gray-500 dark:text-gray-300 text-xs'
                    ])
                    ->columnSpan(3),
                Tables\Columns\TextColumn::make('price_foreign')
                        ->label('Цена для иностр')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        return number_format($state, 0, '.', ' ') . " сум";  // Masalan, 1000.50 ni 1,000.50 formatida
                    })
                    ->extraAttributes([
                        'class' => 'text-gray-500 dark:text-gray-300 text-xs'
                    ])
                    ->columnSpan(3),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->modal()
                    ->modalHeading('Изменение')
                    ->modalWidth('lg')
                    ->modalAlignment('end')
                    ->using(function (Inspection $record, array $data): Inspection {
                        // Filial ma'lumotlarini yangilash
                        $record->update([
                            'name' => $data['name'],
                            'price' => $data['price'],
                        ]);

                        Notification::make()
                            ->title('Осмотр табыслы редакторланды!')
                            ->success()
                            ->send();

                        return $record;
                    }),
                    DeleteAction::make()->label('Удалить'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function getNavigationLabel(): string
    {
        return 'Осмотр'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Осмотр'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Осмотр'; // Rus tilidagi ko'plik shakli
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInspections::route('/'),
        ];
    }
}

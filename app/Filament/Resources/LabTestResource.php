<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LabTestResource\Pages;
use App\Filament\Resources\LabTestResource\RelationManagers;
use App\Models\LabTest;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LabTestResource extends Resource
{
    protected static ?string $model = LabTest::class;
    
    protected static ?string $navigationGroup = 'Услуги';
    protected static ?int $navigationSort = 1;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                ->schema([
                    TextInput::make('name')
                        ->label('Название')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)->columnSpan(12),
                    TextInput::make('price')
                        ->label('Цена')
                        ->required()
                        ->maxLength(255)->columnSpan(12)
                ])->columns(12)->columnSpan(12)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            
            ->headerActions([
                CreateAction::make()
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Название')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->label('Цена за сутки')
                            ->numeric(),
                    ])
                    ->slideOver()
                    ->modalWidth(MaxWidth::Medium)
                    ->action(function (array $data) {
                            LabTest::create([
                                'name' => $data['name'],
                                'price' => $data['price'],
                            ]);

                            Notification::make()
                                ->title('Анализ табыслы жаратылды!')
                                ->success()
                                ->send();
                        }),
            ])
            ->columns([
                
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
            ])
            ->defaultSort('id','desc')
            ->defaultPaginationPageOption(50)
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->modal()
                    ->modalHeading('Изменение')
                    ->modalWidth('lg')
                    ->modalAlignment('end')
                    ->using(function (LabTest $record, array $data): LabTest {
                        // Filial ma'lumotlarini yangilash
                        $record->update([
                            'name' => $data['name'],
                            'price' => $data['price'],
                        ]);

                        Notification::make()
                            ->title('Анализ табыслы редакторланды!')
                            ->success()
                            ->send();

                        return $record;
                    }),
                    DeleteAction::make()->label('Удалить'),
            ]);
    }

    public static function getNavigationLabel(): string
    {
        return 'Анализы'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Анализы'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Анализы'; // Rus tilidagi ko'plik shakli
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
            'index' => Pages\ListLabTests::route('/'),
            // 'create' => Pages\CreateLabTest::route('/create'),
            // 'edit' => Pages\EditLabTest::route('/{record}/edit'),
        ];
    }
}

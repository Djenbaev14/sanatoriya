<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcedureResource\Pages;
use App\Filament\Resources\ProcedureResource\RelationManagers;
use App\Models\Procedure;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProcedureResource extends Resource
{
    protected static ?string $model = Procedure::class;
    
    protected static ?string $navigationGroup = 'Настройка';
    protected static ?int $navigationSort = 2;


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
                    TextInput::make('price_per_day')
                        ->label('Цена')
                        ->required()
                        ->maxLength(255)->columnSpan(12),
                    TextInput::make('price_foreign')
                        ->label('Иностранная цена')
                        ->required()
                        ->maxLength(255)->columnSpan(12),
                        // is_operation
                    Radio::make('is_operation')
                        ->label('Операция?')
                        ->required()
                        ->options([
                            0 => 'Нет',
                            1 => 'Да',
                        ])
                        ->inline()
                        ->columnSpan(12),
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
                            Procedure::create([
                                'name' => $data['name'],
                                'price_per_day' => $data['price_per_day'],
                                'price_foreign'=> $data['price_foreign'],
                                'is_operation'=> $data['is_operation'],
                            ]);
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
                Tables\Columns\TextColumn::make('price_per_day')
                    ->label(label: 'Цена')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        return number_format($state, 0, '.', ' ') . " сум";  // Masalan, 1000.50 ni 1,000.50 formatida
                    })
                    ->extraAttributes([
                        'class' => 'text-gray-500 dark:text-gray-300 text-xs'
                    ])
                    ->columnSpan(3),
                Tables\Columns\TextColumn::make('price_foreign')
                    ->label('Иностранная цена')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        return number_format($state, 0, '.', ' ') . " сум";  // Masalan, 1000.50 ni 1,000.50 formatida
                    })
                    ->extraAttributes([
                        'class' => 'text-gray-500 dark:text-gray-300 text-xs'
                    ])
                    ->columnSpan(3),
                IconColumn::make('is_operation')
                    ->label('Операция')
                    ->boolean()
                    // ->getStateUsing(fn ($record) => !is_null($record->medicalInspection))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
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
                    ->using(function (Procedure $record, array $data): Procedure {
                        // Filial ma'lumotlarini yangilash
                        $record->update([
                                'name' => $data['name'],
                                'price_per_day' => $data['price_per_day'],
                                'price_foreign'=> $data['price_foreign'],
                                'is_operation'=> $data['is_operation'],
                        ]);


                        return $record;
                    }),
                    DeleteAction::make()->label('Удалить'),
            ]);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('настройки');
    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function getNavigationLabel(): string
    {
        return 'Процедуры'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Процедуры'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Процедуры'; // Rus tilidagi ko'plik shakli
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProcedures::route('/'),
            // 'create' => Pages\CreateProcedure::route('/create'),
            // 'edit' => Pages\EditProcedure::route('/{record}/edit'),
        ];
    }
}

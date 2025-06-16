<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcedureResource\Pages;
use App\Filament\Resources\ProcedureResource\RelationManagers;
use App\Models\Procedure;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProcedureResource extends Resource
{
    protected static ?string $model = Procedure::class;
    
    protected static ?string $navigationGroup = 'Услуги';
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
                        ->label('Ценв')
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
                        Forms\Components\TextInput::make('price_per_day')
                            ->required()
                            ->label('Цена за сутки')
                            ->numeric(),
                    ])
                    ->slideOver()
                    ->modalWidth(MaxWidth::Medium)
                    ->action(function (array $data) {
                            Procedure::create([
                                'name' => $data['name'],
                                'price_per_day' => $data['price_per_day'],
                            ]);

                            Notification::make()
                                ->title('Процедура табыслы жаратылды!')
                                ->success()
                                ->send();
                        }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->extraAttributes([
                        'class' => 'text-gray-500 dark:text-gray-300 text-xs'
                    ])
                    ->columnSpan(3),
                Tables\Columns\TextColumn::make('price_per_day')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        return number_format($state, 0, '.', ' ') . " сум";  // Masalan, 1000.50 ni 1,000.50 formatida
                    })
                    ->extraAttributes([
                        'class' => 'text-gray-500 dark:text-gray-300 text-xs'
                    ])
                    ->columnSpan(3),
            ])
            ->defaultSort('created_at','desc')
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
                        ]);

                        Notification::make()
                            ->title('Процедура табыслы редакторланды!')
                            ->success()
                            ->send();

                        return $record;
                    }),
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

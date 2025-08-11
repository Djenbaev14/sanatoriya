<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MkbClassResource\Pages;
use App\Filament\Resources\MkbClassResource\RelationManagers;
use App\Models\MkbClass;
use App\Models\Procedure;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MkbClassResource extends Resource
{
    protected static ?string $model = MkbClass::class;


    protected static ?string $navigationGroup = 'Настройка';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->columnSpan(12)
                            ->maxLength(255),
                        Select::make('procedures')
                            ->label('Процедуры')
                            ->relationship('procedures', 'name') // relationship nomi va ko‘rsatiladigan ustun
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                        Select::make('labTests')
                            ->label('Анализы')
                            ->relationship('labTests', 'name') // relationship nomi va ko‘rsatiladigan ustun
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                    ])->columns(12)->columnSpan(12)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->limit(50),
                BadgeColumn::make('procedures')
                    ->label('Процедуры')
                    ->getStateUsing(function ($record) {
                        return $record->procedures->pluck('name')->toArray();
                    })
                    ->colors(['primary'])
                    ->limit(25),
                BadgeColumn::make('labTests')
                    ->label('Анализы')
                    ->getStateUsing(function ($record) {
                        return $record->labTests->pluck('name')->toArray();
                    })
                    ->colors(['primary'])
                    ->limit(25)
            ])
            ->defaultPaginationPageOption(50)
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                //     ->modal()
                //     ->modalHeading('Изменение')
                //     ->modalWidth('lg')
                //     ->modalAlignment('end')
                //     ->using(function (Procedure $record, array $data): Procedure {
                //         // Filial ma'lumotlarini yangilash
                //         $record->update([
                //                 'name' => $data['name'],
                //         ]);


                //         return $record;
                //     }),
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
        return 'Диагнозы'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Диагнозы'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Диагнозы'; // Rus tilidagi ko'plik shakli
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMkbClasses::route('/'),
            'create' => Pages\CreateMkbClass::route('/create'),
            'edit' => Pages\EditMkbClass::route('/edit/{record}'),
        ];
    }
}

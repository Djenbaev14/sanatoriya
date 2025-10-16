<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FunctionDiagnosticResource\Pages;
use App\Filament\Resources\FunctionDiagnosticResource\RelationManagers;
use App\Models\FunctionDiagnostic;
use App\Models\Procedure;
use App\Models\ProcedureRole;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
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

class FunctionDiagnosticResource extends Resource
{
    protected static ?string $model = Procedure::class;
    
    protected static ?string $navigationGroup = 'Настройка';
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
                    TextInput::make('price_per_day')
                        ->label('Цена')
                        ->required()
                        ->maxLength(255)->columnSpan(12),
                    TextInput::make('price_foreign')
                        ->label('Иностранная цена')
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
                            $procedure=Procedure::create([
                                'name' => $data['name'],
                                'price_per_day' => $data['price_per_day'],
                                'price_foreign'=> $data['price_foreign'],
                                'is_operation'=> 0,
                                'is_treatment'=> 1,
                            ]);
                        }),
            ])
            ->query(
                Procedure::query()
                    ->where('is_operation', 0)
                    ->where('is_treatment', 1)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes([
                        'class' => 'text-gray-500 dark:text-gray-300 text-xs'
                    ])
                    ->columnSpan(3),
                Tables\Columns\TextColumn::make('price_per_day')
                    ->label(label: 'Цена')
                    ->searchable()
                    ->sortable()
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
                TextColumn::make('roles.name')
                    ->label('Роль'),
            ])
            ->defaultSort('id','desc')
            ->defaultPaginationPageOption(50)
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
        return 'Функциональная диагностика'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Функциональная диагностика'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Функциональная диагностика'; // Rus tilidagi ko'plik shakli
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFunctionDiagnostics::route('/'),
        ];
    }
}

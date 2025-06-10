<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyServiceResource\Pages;
use App\Filament\Resources\DailyServiceResource\RelationManagers;
use App\Models\DailyService;
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

class DailyServiceResource extends Resource
{
    protected static ?string $model = DailyService::class;
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
                        TextInput::make('price_per_day')
                            ->label('Цена')
                            ->required()
                            ->numeric()->columnSpan(12),
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
                            $dailyservice = DailyService::create([
                                'name' => $data['name'],
                                'price_per_day' => $data['price_per_day'],
                            ]);

                            Notification::make()
                                ->title('Кунлик сервис табыслы жаратылды!')
                                ->success()
                                ->send();
                        }),
            ])
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\Layout\Grid::make()
                        ->schema([

                            Tables\Columns\Layout\Grid::make()
                                ->schema([
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
                                ->extraAttributes([
                                    'class' => 'mt-2 -mr-6 rtl:-ml-6 rtl:mr-0'
                                ])
                                ->columns(3),
                        ])
                        ->columns(1),
                ]),
            ])
            ->defaultSort('created_at','desc')
            ->contentGrid([
                'md' => 2,
                'xl' => 4,
                '2xl' => 5,
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
                    ->using(function (DailyService $record, array $data): DailyService {
                        // Filial ma'lumotlarini yangilash
                        $record->update([
                            'name' => $data['name'],
                            'price_per_day' => $data['price_per_day'],
                        ]);

                        Notification::make()
                            ->title('Кунлик сервис табыслы редакторланды!')
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
        return 'Ежедневные услуги'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Ежедневные услуги'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Ежедневные услуги'; // Rus tilidagi ko'plik shakli
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyServices::route('/'),
            // 'create' => Pages\CreateDailyService::route('/create'),
            // 'edit' => Pages\EditDailyService::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WardResource\Pages;
use App\Filament\Resources\WardResource\RelationManagers;
use App\Models\Tariff;
use App\Models\Ward;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WardResource extends Resource
{
    protected static ?string $model = Ward::class;
    protected static ?string $navigationGroup = 'Настройка';
    protected static ?int $navigationSort = 4;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->label('Название')
                            ->maxLength(255)
                            ->columnSpan(12),
                        Select::make('tariff_id')
                            ->label('Тариф')
                            ->options(Tariff::pluck('name', 'id')->toArray())
                            ->required()
                            ->columnSpan(12),
                        Repeater::make('beds')
                            ->label('Койка')
                            ->relationship('beds')
                            ->schema([
                                TextInput::make('number')
                                    ->label('Номер')
                                    ->required()
                                    ->columnSpan(12),
                            ])
                            ->columnSpan(12)->columns(12),
                    ])->columns(12)->columnSpan(12)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()
                    ->slideOver()
                    ->modalWidth(MaxWidth::Medium),
                    Action::make('view_tariffs')
                    ->label('Тарифлар')
                    ->color('info')
                    ->modalHeading('Тарифлар')
                    ->modalDescription('Barcha mavjud yotoqxona tariflari ro\'yxati')
                    ->modalWidth(MaxWidth::Medium)
                    ->form(function () {
                        return Tariff::query()
                            ->select('id', 'name', 'daily_price')
                            ->get()
                            ->map(fn ($tariff) =>
                                Placeholder::make('tariff_' . $tariff->id)
                                    ->label($tariff->name)
                                    ->content(number_format($tariff->daily_price, 2) . ' сум / кун')
                            )
                            ->toArray();
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Назад')
            ])
            ->columns([
                                    Tables\Columns\TextColumn::make('name')
                                        ->label('Палата')
                                        ->searchable()
                                        ->columnSpan(6),
                                    Tables\Columns\TextColumn::make('tariff.name')
                                        ->label('Тарифф')
                                        ->searchable()
                                        ->columnSpan(6),
                                    TextColumn::make('beds')
                                        ->label('Койка')
                                        ->getStateUsing(function ($record) {
                                            return $record->beds->map(function ($bed) {
                                                return $bed->number . ' ';
                                            })->join(', ');
                                        })
                                        ->columnSpan(12),
                
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
        return 'Койка'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Койка'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Койка'; // Rus tilidagi ko'plik shakli
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWards::route('/'),
        ];
    }
}

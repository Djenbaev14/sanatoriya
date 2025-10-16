<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcedureResource\Pages;
use App\Filament\Resources\ProcedureResource\RelationManagers;
use App\Models\MkbClass;
use App\Models\Procedure;
use App\Models\ProcedureRole;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

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
                    Select::make('time_category_id')
                            ->options(
                                \App\Models\TimeCategory::all()->pluck('name', 'id')
                            )
                            ->label('Категория времени')
                            ->preload()
                            ->searchable()
                            ->columnSpan(12),
                    Select::make('users')
                            ->options(
                                \App\Models\User::whereHas('roles', function ($q) {
                                    $q->whereIn('name', ['физиотерапия мийирбикеси', 'физиотерапия медбрат']);
                                })->pluck('name', 'id')
                            )
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record) {
                                    $component->state(
                                        \App\Models\ProcedureRole::where('procedure_id', $record->id)->pluck('user_id')->toArray()
                                    );
                                }
                            })
                            ->label('Пользователи')
                            ->preload()
                            ->multiple()
                            ->searchable()
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
                            $procedure=Procedure::create([
                                'name' => $data['name'],
                                'price_per_day' => $data['price_per_day'],
                                'price_foreign'=> $data['price_foreign'],
                                'is_operation'=> 0,
                                'time_category_id'=> $data['time_category_id'],
                                'is_treatment'=> 0,
                            ]);
                            if (!empty($data['users'])) {
                                $procedureRoles = collect($data['users'])->map(fn($userId) => [
                                    'procedure_id' => $procedure->id,
                                    'user_id' => $userId,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ])->toArray();

                                ProcedureRole::insert($procedureRoles);
                            }
                        }),
            ])
            ->query(
                Procedure::query()
                    ->where('is_operation', 0)
                    ->where('is_treatment', 0)
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
                TextColumn::make('users.name')
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
                                'time_category_id'=> $data['time_category_id'],
                        ]);
                        if (!empty($data['users'])) {
                            // Avval eski foydalanuvchilarni tozalaymiz
                            ProcedureRole::where('procedure_id', $record->id)->delete();

                            // Yangi foydalanuvchilarni kiritamiz
                            $procedureRoles = collect($data['users'])->map(fn($userId) => [
                                'procedure_id' => $record->id,
                                'user_id' => $userId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ])->toArray();

                            ProcedureRole::insert($procedureRoles);
                        }


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
        return 'Физиотерапевтическое лечение'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'физиотерапевтическое лечение'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'физиотерапевтическое лечение'; // Rus tilidagi ko'plik shakli
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

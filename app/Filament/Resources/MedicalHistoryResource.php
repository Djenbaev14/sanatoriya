<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalHistoryResource\Pages;
use App\Filament\Resources\MedicalHistoryResource\RelationManagers;
use App\Models\DailyService;
use App\Models\LabTest;
use App\Models\MedicalHistory;
use App\Models\Patient;
use App\Models\Procedure;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MedicalHistoryResource extends Resource
{
    protected static ?string $model = MedicalHistory::class;
    protected static ?string $navigationIcon = 'fas-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Пациент хакида')->schema([
                            Select::make('patient_id')
                                ->label('Пациент')
                                ->options(Patient::orderBy('id','desc')->get()->pluck('full_name','id'))
                                ->required()
                                ->reactive()
                                ->searchable()
                                ->columnSpan(12)
                                ->createOptionForm([
                                    Group::make()
                                        ->schema([
                                            TextInput::make('full_name')
                                                ->label('ФИО')
                                                ->required()
                                                ->maxLength(255)
                                                ->columnSpan(12),
                                            DatePicker::make('birth_date')
                                                ->label('День рождения')
                                                ->columnSpan(12),
                                            Radio::make('gender')
                                                ->label('Jinsi:')
                                                ->options([
                                                    'male' => 'Erkak',
                                                    'female' => 'Ayol',
                                                ])
                                                ->inline() // yonma-yon chiqishi uchun
                                                ->required()
                                                ->columnSpan(12),
                                            Textarea::make('address')
                                                    ->label('Адрес')
                                                    ->columnSpan(12),
                                            TextInput::make('profession')
                                                ->maxLength(255)
                                                ->label('Иш жойи,лавозими')
                                                ->columnSpan(12),
                                            TextInput::make('phone')
                                                ->label('Телефон номер')
                                                ->tel()
                                                ->maxLength(255)
                                                ->columnSpan(12),
                                        ])->columns(12)->columnSpan(12)
                                ])
                                ->createOptionUsing(function (array $data) {
                                    return Patient::create($data)->id; // ❗️ID qaytariladi va patient_id ga qo‘yiladi
                                }),
                            TextInput::make('height')
                                    ->label('рост')
                                    ->suffix('sm')
                                    ->columnSpan(4),
                            TextInput::make('weight')
                                    ->label('вес')
                                    ->suffix('kg')
                                    ->columnSpan(4),
                            TextInput::make('temperature')
                                    ->label('температура')
                                    ->suffix('°C')
                                    ->columnSpan(4),
                            Textarea::make('type_disability')
                                    ->label('Тип инвалидности')
                                    ->columnSpan(12),
                        ])->columns(12)->columnSpan(8),
                        Section::make('Пациент хакида')->schema([
                            Placeholder::make('full_name')
                                ->label('ФИО')
                                ->content(fn (Get $get) => Patient::find($get('patient_id'))->full_name ?? '-')->columnSpan(12),
                            Placeholder::make('birth_date')
                                ->label('День рождения')
                                ->content(fn (Get $get) => Patient::find($get('patient_id'))->birth_date ?? '-')->columnSpan(12),
                            Placeholder::make('gender')
                            ->label('Пол')
                            ->content(fn (Get $get) => match (Patient::find($get('patient_id'))?->gender) {
                                'male' => 'Мужчина',
                                'female' => 'Женщина',
                                default => '-',
                            })
                            ->columnSpan(12),
                            Placeholder::make('phone')
                                ->label('Телефон номер')
                                ->content(fn (Get $get) => Patient::find($get('patient_id'))->phone ?? '-')->columnSpan(12),
                        ])->columns(12)->columnSpan(4),
                        // Section::make()
                        //     ->schema([
                        //         TextInput::make('sessions')
                        //             ->label('Кол сеансов')
                        //             ->numeric()
                        //             ->required()
                        //             ->columnSpan(6),
                        //         DatePicker::make('admission_date')
                        //             ->label('Дата поступления')
                        //             ->columnSpan(6),
                        //         DatePicker::make('discharge_date')
                        //             ->label('Дата выписки')
                        //             ->columnSpan(6),  
                        //     ])->columns(12)->columnSpan(8),
                        // Section::make('Процедуры')
                        //     ->schema([
                        //         Repeater::make('assigned_procedures')
                        //             ->label('')
                        //             ->relationship() // Agar relationship bor bo‘lsa
                        //             ->schema([
                        //                 Select::make('procedure_id')
                        //                     ->label('Тип процедура')
                        //                     ->options(Procedure::all()->pluck('name', 'id'))
                        //                     ->searchable()
                        //                     ->required()
                        //                     ->reactive()
                        //                     ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                        //                         $price = Procedure::find($state)?->price_per_day ?? 0;
                        //                         $set('price', $price);
                        //                         $set('total_price', $price * ($get('sessions') ?? 1));
                                                
                        //                         static::recalculateTotalSum($get, $set);
                        //                     })
                        //                     ->columnSpan(4),

                        //                 TextInput::make('price')
                        //                     ->label('Цена')
                        //                     ->disabled()
                        //                     ->numeric()
                        //                     ->columnSpan(3),

                        //                 TextInput::make('sessions')
                        //                     ->label('Кол сеансов')
                        //                     ->numeric()
                        //                     ->default(1)
                        //                     ->required()
                        //                     ->reactive()
                        //                     ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        //                         $set('total_price', ($get('price') ?? 0) * ($state ?? 1));
                                                
                        //                         static::recalculateTotalSum($get, $set);
                        //                     })
                        //                     ->columnSpan(2),

                        //                 TextInput::make('total_price')
                        //                     ->label('Общая стоимость')
                        //                     ->disabled()
                        //                     ->numeric()
                        //                     ->columnSpan(3)
                        //                     ->afterStateUpdated(function (Get $get, Set $set) {
                        //                         static::recalculateTotalSum($get, $set);
                        //                     }),
                        //             ])->columns(12)->columnSpan(12),
                        //             Placeholder::make('total_sum')
                        //                 ->label('Общая стоимость (всего)')
                        //                 ->content(function (Get $get) {
                        //                     $items = $get('assigned_procedures') ?? [];
                        //                     $total = collect($items)->sum('total_price');
                        //                     return number_format($total, 2, '.', ' ') . ' swm';
                        //                 })
                        //                 ->columnSpanFull(), 
                        //     ])->columns(12)->columnSpan(12),
                        Section::make('Анализи')
                            ->schema([
                                Repeater::make('lab_test_histories')
                                    ->label('')
                                    ->relationship('lab_test_histories') // Agar relationship bor bo‘lsa
                                    ->schema([
                                        Select::make('lab_test_id')
                                            ->label('Тип анализ')
                                            ->options(LabTest::all()->pluck('name', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                $price = LabTest::find($state)?->price ?? 0;
                                                $set('price', $price);
                                                $set('total_price', $price);
                                                
                                            })
                                            ->columnSpan(6),

                                        TextInput::make('price')
                                            ->label('Цена')
                                            ->disabled()
                                            ->numeric()
                                            ->columnSpan(6),

                                    ])
                                    ->columns(12)
                                    ->columnSpan(12),
                                    Placeholder::make('total_sum')
                                        ->label('Общая стоимость (всего)')
                                        ->content(function (Get $get) {
                                            $items = $get('lab_test_histories') ?? [];
                                            $total = collect($items)->sum('price');
                                            return number_format($total, 2, '.', ' ') . ' swm';
                                        })
                                        ->columnSpanFull(), 
                            ])->columns(12)->columnSpan(8),
                    ])->columns(12)->columnSpan(8)
            ]);
    }

    protected static function recalculateTotalSum(Get $get, Set $set): void
    {
        $items = $get('assigned_procedures') ?? [];
        $total = collect($items)->sum('total_price');
        $set('total_sum', $total);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('patient.full_name')
                    ->numeric()
                    ->sortable(),
                // TextColumn::make(name: 'admission_date')
                //     ->date()
                //     ->sortable(),
                // TextColumn::make('discharge_date')
                //     ->date()
                //     ->sortable(),
                TextColumn::make('created_at')
                    ->label('')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getNavigationLabel(): string
    {
        return 'Истории'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Истории'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Истории'; // Rus tilidagi ko'plik shakli
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
            'index' => Pages\ListMedicalHistories::route('/'),
            'create' => Pages\CreateMedicalHistory::route('/create'),
            'edit' => Pages\EditMedicalHistory::route('/{record}/edit'),
        ];
    }
}

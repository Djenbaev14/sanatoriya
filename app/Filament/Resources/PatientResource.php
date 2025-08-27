<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientResource\Pages;
use App\Filament\Resources\PatientResource\RelationManagers;
use App\Models\Country;
use App\Models\District;
use App\Models\Patient;
use App\Models\Region;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'fas-users';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        TextInput::make('full_name')
                            ->label('ФИО')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(12),
                        TextInput::make('passport')
                            ->label('Паспорт серия и номер')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->regex('/^[A-Z]{2}\d{7}$/', 'Паспорт серия и номер должен быть в формате AA1234567')
                            ->placeholder('AA1234567')
                            ->columnSpan(6),
                        DatePicker::make('birth_date')
                            ->label('День рождения')
                            ->required()
                            ->columnSpan(6),
                        Select::make('country_id') 
                            ->label('Страна ') 
                            ->required()
                            ->options(function () { 
                                return Country::all()->mapWithKeys(function ($region) { 
                                    return [$region->id => $region->name]; 
                                }); 
                            }) 
                            ->reactive() 
                            ->required()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                $is_foreign = Country::find($state)?->is_foreign ?? 0;
                                $set('is_foreign', $is_foreign);
                            })
                            ->columnSpan(6),
                        Select::make('region_id') 
                            ->label('Регион ') 
                            ->required()
                            ->options(function (Get $get) { 
                                $countryID = $get('country_id'); 
                                if (!$countryID) return []; 
                                
                                return Region::where('country_id', $countryID)
                                    ->get()
                                    ->mapWithKeys(function ($country) {
                                        return [$country->id => $country->name];
                                    });
                            })
                            ->reactive() 
                            ->required()
                            ->columnSpan(6), 
                        Hidden::make('is_foreign')
                            ->default(0),
                        Select::make('district_id') 
                            ->label('Район ') 
                            ->required()
                            ->options(function (Get $get) { 
                                $regionID = $get('region_id'); 
                                if (!$regionID) return []; 
                                
                                return District::where('region_id', $regionID)
                                    ->get()
                                    ->mapWithKeys(function ($district) {
                                        return [$district->id => $district->name];
                                    });
                            }) 
                            ->reactive() 
                            ->required()
                            ->columnSpan(6), 
                        // is_accomplice uchun 
                        Radio::make('is_accomplice')
                            ->label('Партнёр?')
                            ->required()
                            ->options([
                                0 => 'Нет',
                                1 => 'Да',
                            ])
                            ->inline()
                            ->live()
                            ->columnSpan(6),
                        Select::make('main_patient_id')
                            ->label('Основной пациент')
                            ->options(
                                \App\Models\Patient::where('is_accomplice', false)->pluck('full_name', 'id')
                            )
                            ->searchable()
                            ->required(fn (Get $get) => $get('is_accomplice') == 1)
                            ->visible(fn (Get $get) => $get('is_accomplice') == 1)
                            ->columnSpan(6),
                        Textarea::make('address')
                                ->label('Адрес')
                                ->columnSpan(12),
                        Select::make('gender') 
                            ->label('Пол ')
                            ->options([
                                'male' => 'Мужской',
                                'female' => 'Женской',
                            ])
                            ->required()
                            ->columnSpan(6), 
                        TextInput::make('profession')
                            ->maxLength(255)
                            ->required()
                            ->label('Место работы, должность')
                            ->columnSpan(6),
                        DateTimePicker::make('created_at')
                            ->label('Дата регистрации')
                            ->reactive()
                            ->default(Carbon::now())
                            ->columnSpan(6),
                    ])->columns(12)->columnSpan(12)
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()
                    ->visible(fn () => auth()->user()->can( 'создать больной'))
                    ->modalWidth(MaxWidth::TwoExtraLarge)
                    ->action(function (array $data) {
                            $patient = Patient::create([
                                'full_name' => $data['full_name'],
                                'birth_date' => $data['birth_date'],
                                'gender' => $data['gender'],
                                'country_id' => $data['country_id'],
                                'region_id' => $data['region_id'],
                                'district_id' => $data['district_id'],
                                'passport' => $data['passport'],
                                'address' => $data['address'],
                                'profession' => $data['profession'],
                                'created_at' => $data['created_at'],
                                'is_accomplice' => $data['is_accomplice'],
                                'main_patient_id' => array_key_exists('main_patient_id', $data) ? $data['main_patient_id'] : null,
                                'is_foreign' => $data['is_foreign'],
                            ]);

                            Notification::make()
                                ->title($patient->full_name.' табыслы жаратылды!')
                                ->success()
                                ->send();
                        }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('ФИО')
                    ->searchable(),
                Tables\Columns\TextColumn::make('birth_date')
                    ->label('День рождения')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Время создания')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id','desc')
            ->defaultPaginationPageOption(50)
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->modal()
                    ->slideOver()
                    ->modalHeading('Изменение')
                    ->modalWidth('lg')
                    ->modalAlignment('end')
                    ->using(function (Patient $record, array $data): Patient {
                        // Filial ma'lumotlarini yangilash
                        $record->update([
                                'full_name' => $data['full_name'],
                                'birth_date' => $data['birth_date'],
                                'gender' => $data['gender'],
                                'address' => $data['address'],
                                'profession' => $data['profession'],
                        ]);

                        Notification::make()
                            ->title($data['full_name'].' табыслы редакторланды!')
                            ->success()
                            ->send();

                        return $record;
                    }),
            ]);
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->can('больные');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function getNavigationLabel(): string
    {
        return 'Регистрация'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Регистрация'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Регистрация'; // Rus tilidagi ko'plik shakli
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatients::route('/'),
            'edit' => Pages\EditPatient::route('/{record}/edit'),
            'view' => Pages\ViewPatient::route('/{record}'),
        ];
    }
}

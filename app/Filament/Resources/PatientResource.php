<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientResource\Pages;
use App\Filament\Resources\PatientResource\RelationManagers;
use App\Models\District;
use App\Models\Patient;
use App\Models\Region;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                        TextInput::make('phone')
                            ->label('Телефон номер')
                            ->unique()
                            ->tel()
                            ->maxLength(255)
                            ->columnSpan(12),
                        TextInput::make('full_name')
                            ->label('ФИО')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(6),
                        DatePicker::make('birth_date')
                            ->label('День рождения')
                            ->columnSpan(6),
                        Select::make('region_id') 
                            ->label('Регион ') 
                            ->options(function () { 
                                return Region::all()->mapWithKeys(function ($region) { 
                                    return [$region->id => $region->name]; 
                                }); 
                            }) 
                            ->reactive() 
                            ->required()
                            ->columnSpan(6), 
                        Select::make('district_id') 
                            ->label('Район ') 
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
                            // ->visible(fn (Get $get) => filled($get('region_id'))) 
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
                            ->label('Место работы, должность')
                            ->columnSpan(6),
                    ])->columns(12)->columnSpan(12)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()
                    ->modalWidth(MaxWidth::TwoExtraLarge)
                    ->action(function (array $data) {
                            $patient = Patient::create([
                                'full_name' => $data['full_name'],
                                'birth_date' => $data['birth_date'],
                                'gender' => $data['gender'],
                                'region_id' => $data['region_id'],
                                'district_id' => $data['district_id'],
                                'address' => $data['address'],
                                'profession' => $data['profession'],
                                'phone' => $data['phone'],
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
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон номер')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Время создания')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at','desc')
            ->filters([
                //
            ])
            ->actions([
                // EditAction::make()
                //     ->modal()
                //     ->slideOver()
                //     ->modalHeading('Изменение')
                //     ->modalWidth('lg')
                //     ->modalAlignment('end')
                //     ->using(function (Patient $record, array $data): Patient {
                //         // Filial ma'lumotlarini yangilash
                //         $record->update([
                //                 'full_name' => $data['full_name'],
                //                 'birth_date' => $data['birth_date'],
                //                 'gender' => $data['gender'],
                //                 'address' => $data['address'],
                //                 'profession' => $data['profession'],
                //                 'phone' => $data['phone'],
                //         ]);

                //         Notification::make()
                //             ->title($data['full_name'].' табыслы редакторланды!')
                //             ->success()
                //             ->send();

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

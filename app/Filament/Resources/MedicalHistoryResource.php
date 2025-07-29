<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalHistoryResource\Pages;
use App\Filament\Resources\MedicalHistoryResource\RelationManagers;
use App\Models\AssignedProcedure;
use App\Models\Bed;
use App\Models\Country;
use App\Models\DailyService;
use App\Models\District;
use App\Models\Inspection;
use App\Models\LabTest;
use App\Models\MealType;
use App\Models\MedicalHistory;
use App\Models\MedicalMeal;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Procedure;
use App\Models\Region;
use App\Models\ReturnedProcedure;
use App\Models\Tariff;
use App\Models\Ward;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class MedicalHistoryResource extends Resource
{
    protected static ?string $model = MedicalHistory::class;
    protected static ?string $navigationIcon = 'fas-file-medical';
    public static function form(Form $form): Form{
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('patient_id')
                            ->label('Пациент')
                            ->relationship('patient', 'full_name') // Eager load orqali
                            ->required()
                            ->columnSpan(12)
                            ->searchable(   )
                            ->default(fn () => request()->get('patient_id'))
                            ->options(function () {
                                // Agar URL orqali kelgan bo‘lmasa, barcha bemorlar ro'yxatini chiqaramiz
                                return \App\Models\Patient::pluck('full_name', 'id');
                            })
                            ->suffixAction(
                                \Filament\Forms\Components\Actions\Action::make('create_patient')
                                    ->label('Создать пациента')
                                    ->icon('heroicon-o-plus')
                                    ->form([
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
                                                TextInput::make('phone')
                                                    ->prefix('+998')
                                                    ->label('Телефон номер')
                                                    ->unique(ignoreRecord: true)
                                                    ->required()
                                                    ->tel()
                                                    ->maxLength(255)
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
                                    ])
                                    ->action(function (array $data, Forms\Get $get, Forms\Set $set) {
                                        try{
                                            $patient = Patient::create($data);
                                            Notification::make()
                                                ->title('Пациент успешно создан!')
                                                ->success()
                                                ->send();
                                                $set('patient_id', $patient->id);
                                        }catch (\Exception $e) { 
                                            Notification::make()
                                                ->title('Ошибка при создании пациента: ' . $e->getMessage())
                                                ->danger()
                                                ->send();

                                        }
                                    })
                            )
                            ->disabled(fn () => filled(request()->get('patient_id'))), // Faqat URLda bo‘lsa bloklanadi
                        Hidden::make('created_id')
                            ->default(fn () => auth()->user()->id)
                            ->dehydrated(true),
                        TextInput::make('number')
                            ->label('Номер')
                            ->default(fn () =>MedicalHistory::max('number') + 1)
                            ->required()
                            ->columnSpan(4),
                        TextInput::make('height')
                            ->label('рост')
                            ->required()
                            ->suffix('sm')
                            ->columnSpan(4),
                        TextInput::make('weight')
                            ->label('вес')
                            ->suffix('kg')
                            ->required()
                            ->columnSpan(4),
                        TextInput::make('temperature')
                            ->label('температура')
                            ->suffix('°C')
                            ->required()
                            ->columnSpan(4),
                        Select::make('disability_types')
                            ->label('Типы инвалидности')
                            ->multiple()
                            ->options([
                                'no' => "Yo'q",
                                'physical' => 'Jismoniy',
                                'visual' => 'Ko‘rish',
                                'hearing' => 'Eshitish',
                                'mental' => 'Aqliy',
                                'speech' => 'Nutq',
                                'other' => 'Boshqa',
                            ])
                            ->required()
                            ->searchable()
                            ->columnSpan(4),
                        Select::make('referred_from')
                            ->label('Откуда отправлено?')
                            ->options([
                                'clinic' => '',
                                'hospital' => 'Shifoxona',
                                'emergency' => 'Tez yordam',
                                'self' => 'O‘zi kelgan',
                                'other' => 'Boshqa',
                            ])
                            ->searchable()
                            ->required()
                            ->columnSpan(4),
                        Select::make('transport_type')
                            ->label('Транспортировка')
                            ->options([
                                'ambulance' => 'Tez yordam',
                                'family' => 'Yaqinlari olib kelgan',
                                'self' => 'O‘zi kelgan',
                                'taxi' => 'Taksi',
                                'other' => 'Boshqa',
                            ])
                            ->searchable()
                            ->required()
                            ->columnSpan(4),
                        Textarea::make('side_effects')
                            ->label("Dorilarning nojo'ya ta'siri")
                            ->rows(4)
                            ->placeholder("Masalan: Allergik toshmalar, bosh aylanishi...")
                            ->columnSpan(4),
                        Radio::make('is_emergency')
                            ->required()
                            ->label('Его доставили в экстренном порядке?')
                            ->options([
                                '1' => 'ha',
                                '0'=> "yo'q",
                            ])
                            ->columnSpan(4),
                        DateTimePicker::make('created_at')
                            ->label('Дата создания')
                            ->reactive()
                            ->default(Carbon::now())
                            ->columnSpan(4),
            ])->columns(12)->columnSpan(12),
                    
        ]);
    }

//     public static function getEloquentQuery(): Builder
// {
//     return parent::getEloquentQuery()
//         ->with('accommodation')
//         ->where(function ($query) {
//             $query
//                 // accommodation umuman bo‘lmasa
//                 ->doesntHave('accommodation')
                
//                 // accommodation bo‘lsa, discharge_date null yoki >= bugun bo‘lsa
//                 ->orWhereHas('accommodation', function ($q) {
//                     $q->whereNull('discharge_date')
//                       ->orWhere('discharge_date', '>=', Carbon::today());
//                 });
//         });
// }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('accommodation.admission_date')->label('Дата поступления')
                ->dateTime('d.m.Y H:i')->sortable(),
                BadgeColumn::make('accommodation.discharge_date')
                    ->label('Дата выпуска')
                    ->colors([
                        'danger' => fn ($state) => $state && Carbon::parse($state)->lt(Carbon::today()), // bugundan oldin
                        'success' => fn ($state) => $state && Carbon::parse($state)->gte(Carbon::today()), // bugun yoki keyin
                        'gray' => fn ($state) => is_null($state), // discharge_date null bo‘lsa
                    ])
                    ->dateTime('d.m.Y H:i'), // sana formatlash (xohlasang)
                TextColumn::make('number')->label('Номер')->searchable()->sortable(),
                TextColumn::make('patient.full_name')->label('ФИО')
                ->searchable()
                ->limit(20)
                ->sortable(),
                // biriktirgan vrachin nomin chiqarib bering u medicalInspection da assigned_doctor_id da
                TextColumn::make('medicalInspection.assignedDoctor.name')
                    ->label('Врач')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->medicalInspection?->assignedDoctor?->name ?? 'Не назначен'),
                IconColumn::make('accommodation')
                // Условия размещения ni qisqartib yozib ber
                    ->label('Размещение')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->accommodation))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('medicalInspection')
                    ->label('Приемный')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->medicalInspection))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                // IconColumn::make('departmentInspection')
                //     ->label('Отделение')
                //     ->boolean()
                //     ->getStateUsing(fn ($record) => !is_null($record->departmentInspection))
                //     ->trueIcon('heroicon-o-check-circle')
                //     ->falseIcon('heroicon-o-x-circle')
                //     ->trueColor('success')
                //     ->falseColor('danger'),
                IconColumn::make('labTestHistory')
                    ->label('Анализы')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->labTestHistory))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('assignedProcedure')
                    ->label('Назначенные процедуры')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->assignedProcedure))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                // TextColumn::make('accommodation.discharge_date')->label('Дата выписки')->dateTime()->sortable(),,
            ])
            ->filters([
                SelectFilter::make('doctor')
                    ->label('Врач')
                    ->options(function () {
                        return [
                            'none' => 'Нет врача', // qo‘shildi
                        ] + \App\Models\User::role('Доктор')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (! isset($data['value'])) return $query;

                        if ($data['value'] === 'none') {
                            // medicalInspection mavjud bo‘lmagan yozuvlar
                            return $query->whereDoesntHave('medicalInspection');
                        }

                        return $query->whereHas('medicalInspection', function ($q) use ($data) {
                            $q->where('assigned_doctor_id', $data['value']);
                        });
                    })
            ],layout:FiltersLayout::AboveContent)
            ->defaultPaginationPageOption(50)
            ->defaultSort('number', 'desc')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    // public static function canAccess(): bool
    // {
    //     return auth()->user()?->can('история болезни');
    // }
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('история болезни');
    }

    public static function getNavigationLabel(): string
    {
        return 'Истории Болезни'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Истории Болезни'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Истории Болезни'; // Rus tilidagi ko'plik shakli
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
            'view' => Pages\viewMedicalHistory::route('/{record}'),
        ];
    }
}

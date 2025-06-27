<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccommodationResource\Pages;
use App\Filament\Resources\AccommodationResource\RelationManagers;
use App\Models\Accommodation;
use App\Models\Bed;
use App\Models\Country;
use App\Models\District;
use App\Models\MealType;
use App\Models\MedicalHistory;
use App\Models\Region;
use App\Models\Tariff;
use App\Models\Ward;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccommodationResource extends Resource
{
    protected static ?string $model = Accommodation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                        ->schema([
                        Select::make('patient_id')
                                ->label('Пациент')
                                ->disabled()
                                ->relationship('patient', 'full_name') // yoki kerakli atribut
                                ->default(request()->get('patient_id'))
                                ->required()
                                ->columnSpan(12),
                        Hidden::make('created_id')
                            ->default(fn () => auth()->user()->id)
                            ->dehydrated(true),
                        Select::make('medical_history_id')
                            ->label('История болезно')
                            ->required()
                            ->options(function (Get $get) {
                                $patientId = $get('patient_id');

                                return \App\Models\MedicalHistory::where('patient_id', $patientId)
                                    // ->doesntHave('medicalInspection') // agar faqat bog‘lanmaganlar kerak bo‘lsa
                                    ->get()
                                    ->mapWithKeys(function ($history) {
                                        $formattedId = str_pad('№'.$history->number, 10);
                                        $formattedDate = \Carbon\Carbon::parse($history->created_at)->format('d.m.Y H:i');
                                        return [$history->id => $formattedId . ' - ' . $formattedDate];
                                    });
                            })
                            ->required()
                            ->columnSpan(12),
                        DateTimePicker::make('admission_date')
                            ->label('Дата поступления')
                            ->reactive()
                            ->default(Carbon::now())
                            ->columnSpan(6),
                        DatePicker::make('discharge_date')
                            ->label('Дата выписки')
                            ->reactive()
                            ->columnSpan(6),
                        Group::make()
                            ->schema([
                                
                                    Select::make('tariff_id') 
                                        ->label('Тарифф') 
                                        ->options(function () { 
                                            return Tariff::all()->mapWithKeys(function ($tariff) { 
                                                return [$tariff->id => $tariff->name . ' - ' . number_format($tariff->daily_price, 0) . ' сум']; 
                                            }); 
                                        }) 
                                        ->reactive() 
                                        ->required()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            // Tarif tanlanganda uning narxini tarif_price inputga yozamiz
                                            $price = \App\Models\Tariff::find($state)?->daily_price ?? 0;
                                            $set('tariff_price', $price);
                                        })
                                        ->columnSpan(4), 
                                    Hidden::make('tariff_price')
                                        ->dehydrated(true),
                                    Select::make('ward_id')
                                        ->label('Палата')
                                        ->options(function (Get $get) {
                                            $tariffId = $get('tariff_id');
                                            $currentWardId = $get('ward_id');

                                            if (!$tariffId) return [];

                                            $query = Ward::where('tariff_id', $tariffId);

                                            // Hozirgi tanlangan palatani ham qo‘shamiz, hatto bo‘sh joyi bo‘lmasa ham
                                            if ($currentWardId) {
                                                $query->orWhere('id', $currentWardId);
                                            }

                                            return $query->get()
                                                ->mapWithKeys(function ($ward) {
                                                    return [
                                                        $ward->id => $ward->name . " ({$ward->availableBedsCount} на пустой койке)"
                                                    ];
                                                });
                                        })
                                        ->reactive()
                                        ->required()
                                        ->visible(fn (Get $get) => filled($get('tariff_id')))
                                        ->columnSpan(4),
                                    Select::make('bed_id')
                                        ->label('На пустой койке')
                                        ->options(function (Get $get) {
                                            $wardId = $get('ward_id');
                                            $currentBedId = $get('bed_id');

                                            if (!$wardId) return [];

                                            $query = Bed::query()
                                                ->where('ward_id', $wardId)
                                                ->where(function ($query) use ($currentBedId) {
                                                    $query->availableBeds();

                                                    // Hozirgi tanlangan koykani ham kiritamiz (hatto available bo‘lmasa ham)
                                                    if ($currentBedId) {
                                                        $query->orWhere('id', $currentBedId);
                                                    }
                                                });

                                            return $query->get()
                                                ->mapWithKeys(function ($bed) {
                                                    return [$bed->id => "Койка #{$bed->number}"];
                                                });
                                        })
                                        ->reactive()
                                        ->required()
                                        ->visible(fn (Get $get) => filled($get('ward_id')))
                                        ->columnSpan(4),
                            ])->columns(12)->columnSpan(12),
                                Select::make('meal_type_id')
                                    ->label('Питание')
                                    ->options(function () {
                                        return MealType::all()
                                            ->mapWithKeys(function ($meal_type) {
                                                return [$meal_type->id => $meal_type->name . ' - ' . number_format($meal_type->daily_price, 0, '.', ' ') . ' сум/kun'];
                                            });
                                    })
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Tarif tanlanganda uning narxini tarif_price inputga yozamiz
                                        $price = \App\Models\MealType::find($state)?->daily_price ?? 0;
                                        $set('meal_price', $price);
                                    })
                                    ->columnSpan(4), 
                                    Hidden::make('meal_price')
                                        ->dehydrated(true), 
                                    Group::make()
                                        ->schema([
                                            Radio::make('has_accomplice')
                                                ->label('Есть сопровождающий?')
                                                ->options([
                                                    0 => 'Нет',
                                                    1 => 'Да',
                                                ])
                                                ->inline()
                                                ->live()
                                                ->columnSpan(6),
                                        ])->columns(12)->columnSpan(12)
                        ])->columns(12)->columnSpan(12),
                        
            Section::make('Уход за пациентом')
                ->visible(fn (Get $get) => $get('has_accomplice') == 1)
                ->schema([
                    Select::make('accomplice_patient_id')
                        ->label('Уход за пациентом')
                        ->reactive()
                        ->options(function (Get $get) {
                            $patientId = $get('patient_id');

                            return \App\Models\Patient::where('is_accomplice', 1)
                                ->where('main_patient_id', $patientId)
                                ->get()
                                ->mapWithKeys(function ($patient) {
                                    return [$patient->id => $patient->full_name];
                                });
                        })
                        ->suffixAction(
                            Action::make('add_accomplice')
                                ->label('Добавить сопровождающего')
                                ->icon('heroicon-o-plus')
                                ->form([
                                    Group::make()
                                        ->schema([
                                            TextInput::make('full_name')
                                                ->label('ФИО')
                                                ->required()
                                                ->maxLength(255)
                                                ->columnSpan(12),
                                            TextInput::make('phone')
                                                ->prefix('+998')
                                                ->label('Телефон номер')
                                                ->unique(table: 'patients', column: 'phone')
                                                ->required()
                                                ->tel()
                                                ->maxLength(255)
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
                                        ])->columns(12)->columnSpan(12)
                                ])
                                ->action(function (array $data,Get $get, Set $set) {
                                    $patientId = $get('patient_id');
                                    $accomplicePatient = \App\Models\Patient::create([
                                        'full_name' => $data['full_name'],
                                        'birth_date' => $data['birth_date'],
                                        'gender' => $data['gender'],
                                        'country_id' => $data['country_id'],
                                        'region_id' => $data['region_id'],
                                        'district_id' => $data['district_id'],
                                        'address' => $data['address'],
                                        'profession' => $data['profession'],
                                        'phone' => $data['phone'],
                                        'is_accomplice' => 1,
                                        'is_foreign' => $data['is_foreign'],
                                        'main_patient_id' => $patientId,
                                    ]);
                                    $set('accomplice_patient_id', $accomplicePatient->id);
                                    Notification::make()
                                        ->title('Сопровождающий добавлен')
                                        ->success()
                                        ->send();
                                })
                        )
                        ->required()
                        ->columnSpan(12),
                        
                        DateTimePicker::make('accomplice_admission_date')
                            ->label('Дата поступления')
                            ->reactive()
                            ->default(Carbon::now())
                            ->columnSpan(6),
                        DatePicker::make('accomplice_discharge_date')
                            ->label('Дата выписки')
                            ->reactive()
                            ->columnSpan(6),
                        Group::make()
                            ->schema([
                                Select::make('accomplice_tariff_id') 
                                        ->label('Тарифф') 
                                        ->options(function () { 
                                            return Tariff::all()->mapWithKeys(function ($tariff) { 
                                                return [$tariff->id => $tariff->name . ' - ' . number_format($tariff->partner_daily_price, 0) . ' сум']; 
                                            }); 
                                        }) 
                                        ->reactive() 
                                        ->required()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            // Tarif tanlanganda uning narxini tarif_price inputga yozamiz
                                            $price = \App\Models\Tariff::find($state)?->partner_daily_price ?? 0;
                                            $set('accomplice_tariff_price', $price);
                                        })
                                        ->columnSpan(4), 
                                    Hidden::make('accomplice_tariff_price')
                                        ->dehydrated(true),  
                                Select::make('accomplice_ward_id')
                                        ->label('Палата')
                                        ->options(function (Get $get) {
                                            $tariffId = $get('accomplice_tariff_id');
                                            $currentWardId = $get('accomplice_ward_id');

                                            if (!$tariffId) return [];

                                            $query = Ward::where('tariff_id', $tariffId);

                                            // Hozirgi tanlangan palatani ham qo‘shamiz, hatto bo‘sh joyi bo‘lmasa ham
                                            if ($currentWardId) {
                                                $query->orWhere('id', $currentWardId);
                                            }

                                            return $query->get()
                                                ->mapWithKeys(function ($ward) {
                                                    return [
                                                        $ward->id => $ward->name . " ({$ward->availableBedsCount} на пустой койке)"
                                                    ];
                                                });
                                        })
                                        ->reactive()
                                        ->required()
                                        ->visible(fn (Get $get) => filled($get('tariff_id')))
                                        ->columnSpan(4),
                                Select::make('accomplice_bed_id')
                                        ->label('На пустой койке')
                                        ->options(function (Get $get) {
                                            $wardId = $get('accomplice_ward_id');
                                            $currentBedId = $get('accomplice_bed_id');

                                            if (!$wardId) return [];

                                            $query = Bed::query()
                                                ->where('ward_id', $wardId)
                                                ->where(function ($query) use ($currentBedId) {
                                                    $query->availableBeds();

                                                    // Hozirgi tanlangan koykani ham kiritamiz (hatto available bo‘lmasa ham)
                                                    if ($currentBedId) {
                                                        $query->orWhere('id', $currentBedId);
                                                    }
                                                });

                                            return $query->get()
                                                ->mapWithKeys(function ($bed) {
                                                    return [$bed->id => "Койка #{$bed->number}"];
                                                });
                                        })
                                        ->reactive()
                                        ->required()
                                        ->visible(fn (Get $get) => filled($get('accomplice_ward_id')))
                                        ->columnSpan(4),
                        ])->columns(12)->columnSpan(12),
                                Select::make('accomplice_meal_type_id')
                                    ->label('Питание')
                                    ->options(function () {
                                        return MealType::all()
                                            ->mapWithKeys(function ($meal_type) {
                                                return [$meal_type->id => $meal_type->name . ' - ' . number_format($meal_type->daily_price_foreign, 0, '.', ' ') . ' сум/kun'];
                                            });
                                    })
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Tarif tanlanganda uning narxini tarif_price inputga yozamiz
                                        $price = \App\Models\MealType::find($state)?->daily_price_foreign ?? 0;
                                        $set('accomplice_meal_price', $price);
                                    })
                                    ->columnSpan(4), 
                                    Hidden::make('accomplice_meal_price')
                                        ->dehydrated(true), 
                    ])->columns(12)->columnSpan(12),
                        
                            Section::make('Общая стоимость')
                                ->schema([
                                    // Koyka uchun hisob
                                    Placeholder::make('bed_total')
                                        ->label('Стоимость койки')
                                        ->content(function (Get $get) {
                                            $bedId = $get('bed_id');
                                            $admissionDate = $get('admission_date');
                                            $dischargeDate = $get('discharge_date');

                                            if (!$bedId || !$admissionDate || !$dischargeDate) {
                                                return '0 сум (не выбрано)';
                                            }

                                            $bed = \App\Models\Bed::with('ward.tariff')->find($bedId);
                                            if (!$bed || !$bed->ward || !$bed->ward->tariff) {
                                                return '0 сум (койка не найдена)';
                                            }

                                            $dailyPrice = $bed->ward->tariff->daily_price;

                                            $admission = \Carbon\Carbon::parse($admissionDate);
                                            $discharge = \Carbon\Carbon::parse($dischargeDate);

                                            $days = $admission->diffInDays($discharge) + 1;

                                            // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                            if ($admission->format('H:i') > '12:00' && $days > 0) {
                                                $days -= 1;
                                            }

                                            // Kamida 1 kun hisoblash
                                            $days = max($days, 1);

                                            $total = $dailyPrice * $days;

                                            return number_format($total, 0, '.', ' ') . ' сум (' . $days . ' дней × ' . number_format($dailyPrice, 0, '.', ' ') . ')';
                                        })
                                        ->columnSpan(6),

                                    // Ovqatlanish uchun hisob
                                    Placeholder::make('meal_total')
                                        ->label('Стоимость питания')
                                        ->content(function (Get $get) {
                                            $mealTypeId = $get('meal_type_id');
                                            $admissionDate = $get('admission_date');
                                            $dischargeDate = $get('discharge_date');

                                            if (!$mealTypeId || !$admissionDate || !$dischargeDate) {
                                                return '0 сум (не выбрано)';
                                            }

                                            $mealType = \App\Models\MealType::find($mealTypeId);
                                            if (!$mealType) {
                                                return '0 сум (питание не найдено)';
                                            }

                                            $dailyPrice = $mealType->daily_price;
                                            

                                            $admission = \Carbon\Carbon::parse($admissionDate);
                                            $discharge = \Carbon\Carbon::parse($dischargeDate);

                                            $days = $admission->diffInDays($discharge) + 1;

                                            // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                            if ($admission->format('H:i') > '12:00' && $days > 0) {
                                                $days -= 1;
                                            }
                                            // Kamida 1 kun hisoblash
                                            $days = max($days, 1);

                                            $total = $dailyPrice * $days;
                                            return number_format($total, 0, '.', ' ') . ' сум (' . $days . ' дней × ' . number_format($dailyPrice, 0, '.', ' ') . ')';
                                        })
                                        ->columnSpan(6),
                                    Placeholder::make('partner_bed_total')
                                        ->label('Партнёр стоимость койки')
                                        ->content(function (Get $get) {
                                            $bedId = $get('accomplice_bed_id');
                                            $admissionDate = $get('accomplice_admission_date');
                                            $dischargeDate = $get('accomplice_discharge_date');

                                            if (!$bedId || !$admissionDate || !$dischargeDate) {
                                                return '0 сум (не выбрано)';
                                            }

                                            $bed = \App\Models\Bed::with('ward.tariff')->find($bedId);
                                            if (!$bed || !$bed->ward || !$bed->ward->tariff) {
                                                return '0 сум (койка не найдена)';
                                            }

                                            $dailyPrice = $bed->ward->tariff->partner_daily_price;

                                            $admission = \Carbon\Carbon::parse($admissionDate);
                                            $discharge = \Carbon\Carbon::parse($dischargeDate);

                                            $days = $admission->diffInDays($discharge) + 1;

                                            // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                            if ($admission->format('H:i') > '12:00' && $days > 0) {
                                                $days -= 1;
                                            }

                                            // Kamida 1 kun hisoblash
                                            $days = max($days, 1);

                                            $total = $dailyPrice * $days;

                                            return number_format($total, 0, '.', ' ') . ' сум (' . $days . ' дней × ' . number_format($dailyPrice, 0, '.', ' ') . ')';
                                        })
                                        ->columnSpan(6),

                                    // Ovqatlanish uchun hisob
                                    Placeholder::make('partner_meal_total')
                                        ->label('Партнёр стоимость питания')
                                        ->content(function (Get $get) {
                                            $mealTypeId = $get('accomplice_meal_type_id');
                                            $admissionDate = $get('accomplice_admission_date');
                                            $dischargeDate = $get('accomplice_discharge_date');

                                            if (!$mealTypeId || !$admissionDate || !$dischargeDate) {
                                                return '0 сум (не выбрано)';
                                            }

                                            $mealType = \App\Models\MealType::find($mealTypeId);
                                            if (!$mealType) {
                                                return '0 сум (питание не найдено)';
                                            }

                                            $dailyPrice = $mealType->daily_price_foreign;
                                            

                                            $admission = \Carbon\Carbon::parse($admissionDate);
                                            $discharge = \Carbon\Carbon::parse($dischargeDate);

                                            $days = $admission->diffInDays($discharge) + 1;

                                            // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                            if ($admission->format('H:i') > '12:00' && $days > 0) {
                                                $days -= 1;
                                            }
                                            // Kamida 1 kun hisoblash
                                            $days = max($days, 1);

                                            $total = $dailyPrice * $days;
                                            return number_format($total, 0, '.', ' ') . ' сум (' . $days . ' дней × ' . number_format($dailyPrice, 0, '.', ' ') . ')';
                                        })
                                        ->columnSpan(6),

                                    // Umumiy summa
                                    Placeholder::make('grand_total')
                                        ->label('ОБЩАЯ СТОИМОСТЬ')
                                        ->content(function (Get $get) {
                                            // Koyka
                                            $bedTotal = 0;
                                            $bedId = $get('bed_id');
                                            $admissionDate = $get('admission_date');
                                            $dischargeDate = $get('discharge_date');

                                            if ($bedId && $admissionDate && $dischargeDate) {
                                                $bed = \App\Models\Bed::with('ward.tariff')->find($bedId);
                                                if ($bed) {
                                                    $admission = \Carbon\Carbon::parse($admissionDate);
                                                    $discharge = \Carbon\Carbon::parse($dischargeDate);

                                                    $days = $admission->diffInDays($discharge) + 1;

                                                    // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                                    if ($admission->format('H:i') > '12:00' && $days > 0) {
                                                        $days -= 1;
                                                    }
                                                    // Kamida 1 kun hisoblash
                                                    $days = max($days, 1);
                                                    $bedTotal = $bed->ward->tariff->daily_price * $days;
                                                }
                                            }

                                            // Ovqatlanish
                                            $mealTotal = 0;
                                            $mealTypeId = $get('meal_type_id');

                                            if ($mealTypeId && $admissionDate && $dischargeDate) {
                                                $mealType = \App\Models\MealType::find($mealTypeId);
                                                if ($mealType) {
                                                    
                                                    $admission = \Carbon\Carbon::parse($admissionDate);
                                                    $discharge = \Carbon\Carbon::parse($dischargeDate);

                                                    $days = $admission->diffInDays($discharge) + 1;

                                                    // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                                    if ($admission->format('H:i') > '12:00' && $days > 0) {
                                                        $days -= 1;
                                                    }
                                                    // Kamida 1 kun hisoblash
                                                    $days = max($days, 1);
                                                    $mealTotal = $mealType->daily_price * $days;
                                                }
                                            }
                                            
                                            // // Koyka
                                            $partnerBedTotal = 0;
                                            $partnerBedId = $get('accomplice_bed_id');
                                            $partnerAdmissionDate = $get('accomplice_admission_date');
                                            $partnerDischargeDate = $get('accomplice_discharge_date');

                                            if ($partnerBedId && $partnerAdmissionDate && $partnerDischargeDate) {
                                                $partnerBed = \App\Models\Bed::with('ward.tariff')->find($partnerBedId);
                                                if ($partnerBed) {
                                                    $admission = \Carbon\Carbon::parse($partnerAdmissionDate);
                                                    $discharge = \Carbon\Carbon::parse($partnerDischargeDate);

                                                    $days = $admission->diffInDays($discharge) + 1;

                                                    // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                                    if ($admission->format('H:i') > '12:00' && $days > 0) {
                                                        $days -= 1;
                                                    }
                                                    // Kamida 1 kun hisoblash
                                                    $days = max($days, 1);
                                                    $partnerBedTotal = $partnerBed->ward->tariff->partner_daily_price * $days;
                                                }
                                            }

                                            // Ovqatlanish
                                            $partnerMealTotal = 0;
                                            $partnerMealTypeId = $get('accomplice_meal_type_id');

                                            if ($partnerMealTypeId && $partnerAdmissionDate && $partnerDischargeDate) {
                                                $partnerMealType = \App\Models\MealType::find($partnerMealTypeId);
                                                if ($partnerMealType) {
                                                    
                                                    $admission = \Carbon\Carbon::parse($partnerAdmissionDate);
                                                    $discharge = \Carbon\Carbon::parse($partnerDischargeDate);

                                                    $days = $admission->diffInDays($discharge) + 1;

                                                    // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                                    if ($admission->format('H:i') > '12:00' && $days > 0) {
                                                        $days -= 1;
                                                    }
                                                    // Kamida 1 kun hisoblash
                                                    $days = max($days, 1);
                                                    $partnerMealTotal = $mealType->daily_price_foreign * $days;
                                                }
                                            }

                                            $grandTotal =$bedTotal + $mealTotal + $partnerBedTotal + $partnerMealTotal;

                                            return new \Illuminate\Support\HtmlString("
                                                <div class='bg-blue-50 p-4 rounded-lg border-2 border-blue-200'>
                                                    <div class='text-2xl font-bold text-blue-800'>
                                                        " . number_format($grandTotal, 0, '.', ' ') . " сум
                                                    </div>
                                                </div>
                                            ");
                                        })
                                        ->columnSpan(6),
                                ])
                                ->columns(12)
                                ->columnSpan(12),
            ]);
    }
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccommodations::route('/'),
            'create' => Pages\CreateAccommodation::route('/create'),
            'edit' => Pages\EditAccommodation::route('/{record}/edit'),
            'view' => Pages\ViewAccommodation::route('/{record}'),
        ];
    }
}

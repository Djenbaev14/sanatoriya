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
                            ->default(request()->get('medical_history_id'))
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
                            // medical history vaqtni defaultiga o‘rnatamiz
                            ->default(fn (Get $get) => $get('medical_history_id') ? \App\Models\MedicalHistory::find($get('medical_history_id'))?->created_at : Carbon::now())
                            ->columnSpan(6),
                        DateTimePicker::make('discharge_date')
                            ->label('Дата выписки')
                            ->reactive()
                            ->columnSpan(6),
                        Group::make()
                            ->schema([
                                    Select::make('tariff_id')
                                        ->label('Тариф')
                                        ->options(function (callable $get) {
                                            $patientId = $get('patient_id');
                                            $isForeign = \App\Models\Patient::find($patientId)?->is_foreign ?? false;

                                            return \App\Models\Tariff::all()->mapWithKeys(function ($tariff) use ($isForeign) {
                                                $price = $isForeign ? $tariff->foreign_daily_price : $tariff->daily_price;
                                                $label = $tariff->name . ' - ' . number_format($price, 0, '.', ' ') . ' сум';
                                                return [$tariff->id => $label];
                                            });
                                        })
                                        ->reactive()
                                        ->required()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $tariff = \App\Models\Tariff::find($state);
                                            $patientId = $get('patient_id');
                                            $isForeign = \App\Models\Patient::find($patientId)?->is_foreign ?? false;

                                            $price = $isForeign ? $tariff?->foreign_daily_price : $tariff?->daily_price;
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
                                    ->options(function (callable $get) {
                                            $patientId = $get('patient_id');
                                            $isForeign = \App\Models\Patient::find($patientId)?->is_foreign ?? false;

                                            return \App\Models\MealType::all()->mapWithKeys(function ($meal_type) use ($isForeign) {
                                                $price = $isForeign ? $meal_type->foreign_daily_price : $meal_type->daily_price;
                                                $label = $meal_type->name . ' - ' . number_format($price, 0, '.', ' ') . ' сум';
                                                return [$meal_type->id => $label];
                                            });
                                        })
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $tariff = \App\Models\MealType::find($state);
                                        $patientId = $get('patient_id');
                                        $isForeign = \App\Models\Patient::find($patientId)?->is_foreign ?? false;

                                        $price = $isForeign ? $tariff?->foreign_daily_price : $tariff?->daily_price;
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
                                                ->columnSpan(12),
                                        ])->columns(12)->columnSpan(12)
                                ])
                                ->action(function (array $data,Get $get, Set $set) {
                                    $patientId = $get('patient_id');
                                    $accomplicePatient = \App\Models\Patient::create([
                                        'full_name' => $data['full_name'],
                                        'phone' => $data['phone'],
                                        'is_accomplice' => 1,
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
                                        ->visible(fn (Get $get) => filled($get('accomplice_tariff_id')))
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
                                                return [$meal_type->id => $meal_type->name . ' - ' . number_format($meal_type->partner_daily_price, 0, '.', ' ') . ' сум/kun'];
                                            });
                                    })
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Tarif tanlanganda uning narxini tarif_price inputga yozamiz
                                        $price = \App\Models\MealType::find($state)?->partner_daily_price ?? 0;
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

                                            // $days = $admission->diffInDays($discharge);

                                            // // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                            // if ($admission->format('H:i') > '12:00' && $days > 0) {
                                            //     $days -= 1;
                                            // }
                                            // if($discharge->format('H:i') > '12:00' && $days > 0) {
                                            //     $days += 1;
                                            // }

                                            // Kamida 1 kun hisoblash
                                            // $days = max($days, 1);
                                            
                                            $start = $admission->hour < 12 ? $admission->copy()->startOfDay() : $admission->copy()->addDay()->startOfDay();
                                            $end = $discharge->hour >= 12 ? $discharge->copy()->startOfDay()->addDay() : $discharge->copy()->startOfDay();
                                            $days= max($start->diffInDays($end), 0);

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

                                            // $days = $admission->diffInDays($discharge);

                                            // // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                            // if ($admission->format('H:i') > '12:00' && $days > 0) {
                                            //     $days -= 1;
                                            // }
                                            // if ($discharge->format('H:i') > '12:00' && $days > 0) {
                                            //     $days += 1;
                                            // }
                                            // Kamida 1 kun hisoblash
                                            // $days = max($days, 1);
                                            $start = $admission->hour < 12 ? $admission->copy()->startOfDay() : $admission->copy()->addDay()->startOfDay();
                                            $end = $discharge->hour >= 12 ? $discharge->copy()->startOfDay()->addDay() : $discharge->copy()->startOfDay();
                                            $days= max($start->diffInDays($end), 0);

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

                                            // $days = $admission->diffInDays($discharge);

                                            // // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                            // if ($admission->format('H:i') > '12:00' && $days > 0) {
                                            //     $days -= 1;
                                            // }
                                            // if($discharge->format('H:i') > '12:00' && $days > 0) {
                                            //     $days += 1;
                                            // }

                                            // Kamida 1 kun hisoblash
                                            // $days = max($days, 1);

                                            $start = $admission->hour < 12 ? $admission->copy()->startOfDay() : $admission->copy()->addDay()->startOfDay();
                                            $end = $discharge->hour >= 12 ? $discharge->copy()->startOfDay()->addDay() : $discharge->copy()->startOfDay();
                                            $days= max($start->diffInDays($end), 0);
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

                                            $dailyPrice = $mealType->partner_daily_price;
                                            

                                            $admission = \Carbon\Carbon::parse($admissionDate);
                                            $discharge = \Carbon\Carbon::parse($dischargeDate);

                                            // $days = $admission->diffInDays($discharge);

                                            // // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                            // if ($admission->format('H:i') > '12:00' && $days > 0) {
                                            //     $days -= 1;
                                            // }
                                            // if($discharge->format('H:i') > '12:00' && $days > 0) {
                                            //     $days += 1;
                                            // }
                                            // Kamida 1 kun hisoblash
                                            // $days = max($days, 1);
                                            $start = $admission->hour < 12 ? $admission->copy()->startOfDay() : $admission->copy()->addDay()->startOfDay();
                                            $end = $discharge->hour >= 12 ? $discharge->copy()->startOfDay()->addDay() : $discharge->copy()->startOfDay();
                                            $days= max($start->diffInDays($end), 0);

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
                                            $patientId = $get('patient_id');
                                            $isForeign = \App\Models\Patient::find($patientId)?->is_foreign ?? false;
                                            if ($bedId && $admissionDate && $dischargeDate) {
                                                $bed = \App\Models\Bed::with('ward.tariff')->find($bedId);
                                                if ($bed) {
                                                    $admission = \Carbon\Carbon::parse($admissionDate);
                                                    $discharge = \Carbon\Carbon::parse($dischargeDate);

                                                    // $days = $admission->diffInDays($discharge) ;

                                                    // // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                                    // if ($admission->format('H:i') > '12:00' && $days > 0) {
                                                    //     $days -= 1;
                                                    // }
                                                    // if($discharge->format('H:i') > '12:00' && $days > 0) {
                                                    //     $days += 1;
                                                    // }
                                                    // // Kamida 1 kun hisoblash
                                                    // $days = max($days, 1);
                                                    
                                                    $start = $admission->hour < 12 ? $admission->copy()->startOfDay() : $admission->copy()->addDay()->startOfDay();
                                                    $end = $discharge->hour >= 12 ? $discharge->copy()->startOfDay()->addDay() : $discharge->copy()->startOfDay();
                                                    $days= max($start->diffInDays($end), 0);
                                                    $bedPrice=$isForeign ? $bed->ward->tariff->foreign_daily_price : $bed->ward->tariff->daily_price;
                                                    $bedTotal = $bedPrice * $days;
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

                                                    $days = $admission->diffInDays($discharge);

                                                    // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                                    // if ($admission->format('H:i') > '12:00' && $days > 0) {
                                                    //     $days -= 1;
                                                    // }
                                                    // if($discharge->format('H:i') > '12:00' && $days > 0) {
                                                    //     $days += 1;
                                                    // }
                                                    // // Kamida 1 kun hisoblash
                                                    // $days = max($days, 1);
                                                    
                                            $start = $admission->hour < 12 ? $admission->copy()->startOfDay() : $admission->copy()->addDay()->startOfDay();
                                            $end = $discharge->hour >= 12 ? $discharge->copy()->startOfDay()->addDay() : $discharge->copy()->startOfDay();
                                            $days= max($start->diffInDays($end), 0);

                                                    $mealPrice=$isForeign ? $mealType->foreign_daily_price : $mealType->daily_price;
                                                    $mealTotal = $mealPrice * $days;
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

                                                    // $days = $admission->diffInDays($discharge);

                                                    // // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                                    // if ($admission->format('H:i') > '12:00' && $days > 0) {
                                                    //     $days -= 1;
                                                    // }
                                                    // if($discharge->format('H:i') > '12:00' && $days > 0) {
                                                    //     $days += 1;
                                                    // }
                                                    // // Kamida 1 kun hisoblash
                                                    // $days = max($days, 1);
                                                    
                                            $start = $admission->hour < 12 ? $admission->copy()->startOfDay() : $admission->copy()->addDay()->startOfDay();
                                            $end = $discharge->hour >= 12 ? $discharge->copy()->startOfDay()->addDay() : $discharge->copy()->startOfDay();
                                            $days= max($start->diffInDays($end), 0);
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

                                                    // $days = $admission->diffInDays($discharge);

                                                    // // Agar soat 12:00 dan keyin kelgan bo‘lsa — 1 kun kamaytiramiz
                                                    // if ($admission->format('H:i') > '12:00' && $days > 0) {
                                                    //     $days -= 1;
                                                    // }
                                                    
                                                    // if($discharge->format('H:i') > '12:00' && $days > 0) {
                                                    //     $days += 1;
                                                    // }
                                                    // // Kamida 1 kun hisoblash
                                                    // $days = max($days, 1);
                                                    
                                            $start = $admission->hour < 12 ? $admission->copy()->startOfDay() : $admission->copy()->addDay()->startOfDay();
                                            $end = $discharge->hour >= 12 ? $discharge->copy()->startOfDay()->addDay() : $discharge->copy()->startOfDay();
                                            $days= max($start->diffInDays($end), 0);
                                                    $partnerMealTotal = $mealType->partner_daily_price * $days;
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

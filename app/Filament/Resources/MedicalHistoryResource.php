<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalHistoryResource\Pages;
use App\Filament\Resources\MedicalHistoryResource\RelationManagers;
use App\Models\AssignedProcedure;
use App\Models\Bed;
use App\Models\DailyService;
use App\Models\LabTest;
use App\Models\MealType;
use App\Models\MedicalHistory;
use App\Models\MedicalMeal;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Procedure;
use App\Models\ReturnedProcedure;
use App\Models\Tariff;
use App\Models\Ward;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class MedicalHistoryResource extends Resource
{
    protected static ?string $model = MedicalHistory::class;
    protected static ?string $navigationIcon = 'fas-book-open';

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Wizard::make([
    //                 Wizard\Step::make('История болезно')
    //                     ->schema([
    //                         Section::make('Пациент хакида')->schema([
    //                                     Select::make('patient_id')
    //                                         ->label('Пациент')
    //                                         ->options(Patient::orderBy('id','desc')->get()->pluck('full_name','id'))
    //                                         ->required()
    //                                         ->reactive()
    //                                         ->searchable()
    //                                         ->columnSpan(12)
    //                                         ->createOptionForm([
    //                                             Group::make()
    //                                                 ->schema([
    //                                                     TextInput::make('full_name')
    //                                                         ->label('ФИО')
    //                                                         ->required()
    //                                                         ->maxLength(255)
    //                                                         ->columnSpan(12),
    //                                                     DatePicker::make('birth_date')
    //                                                         ->label('День рождения')
    //                                                         ->columnSpan(12),
    //                                                     Radio::make('gender')
    //                                                         ->label('Jinsi:')
    //                                                         ->options([
    //                                                             'male' => 'Erkak',
    //                                                             'female' => 'Ayol',
    //                                                         ])
    //                                                         ->inline() // yonma-yon chiqishi uchun
    //                                                         ->required()
    //                                                         ->columnSpan(12),
    //                                                     Textarea::make('address')
    //                                                             ->label('Адрес')
    //                                                             ->columnSpan(12),
    //                                                     TextInput::make('profession')
    //                                                         ->maxLength(255)
    //                                                         ->label('Иш жойи,лавозими')
    //                                                         ->columnSpan(12),
    //                                                     TextInput::make('phone')
    //                                                         ->label('Телефон номер')
    //                                                         ->tel()
    //                                                         ->maxLength(255)
    //                                                         ->columnSpan(12),
    //                                                 ])->columns(12)->columnSpan(12)
    //                                         ])
    //                                         ->createOptionUsing(function (array $data) {
    //                                             return Patient::create($data)->id; // ❗️ID qaytariladi va patient_id ga qo‘yiladi
    //                                         }),
    //                                     TextInput::make('height')
    //                                             ->label('рост')
    //                                             ->suffix('sm')
    //                                             ->columnSpan(4),
    //                                     TextInput::make('weight')
    //                                             ->label('вес')
    //                                             ->suffix('kg')
    //                                             ->columnSpan(4),
    //                                     TextInput::make('temperature')
    //                                             ->label('температура')
    //                                             ->suffix('°C')
    //                                             ->columnSpan(4),
    //                                     Textarea::make('type_disability')
    //                                             ->label('Тип инвалидности')
    //                                             ->columnSpan(12),
    //                         ])->columnSpan(6),
    //                         Section::make('Пациент хакида')->schema([
    //                                     Placeholder::make('full_name')
    //                                         ->label('ФИО')
    //                                         ->content(fn (Get $get) => Patient::find($get('patient_id'))->full_name ?? '-')->columnSpan(12),
    //                                     Placeholder::make('birth_date')
    //                                         ->label('День рождения')
    //                                         ->content(fn (Get $get) => Patient::find($get('patient_id'))->birth_date ?? '-')->columnSpan(12),
    //                                     Placeholder::make('gender')
    //                                     ->label('Пол')
    //                                     ->content(fn (Get $get) => match (Patient::find($get('patient_id'))?->gender) {
    //                                         'male' => 'Мужчина',
    //                                         'female' => 'Женщина',
    //                                         default => '-',
    //                                     })
    //                                     ->columnSpan(12),
    //                                     Placeholder::make('phone')
    //                                         ->label('Телефон номер')
    //                                         ->content(fn (Get $get) => Patient::find($get('patient_id'))->phone ?? '-')->columnSpan(12),
    //                         ])->columnSpan(2),
    //                         Section::make('Анализи')
    //                             ->schema([
    //                                         Repeater::make('labTestHistories')
    //                                             ->label('')
    //                                             ->relationship('labTestHistories') // Agar relationship bor bo‘lsa
    //                                             ->schema([
    //                                                 Select::make('lab_test_id')
    //                                                     ->label('Тип анализ')
    //                                                     ->options(LabTest::all()->pluck('name', 'id'))
    //                                                     ->searchable()
    //                                                     ->required()
    //                                                     ->reactive()
    //                                                     ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
    //                                                         $price = LabTest::find($state)?->price ?? 0;
    //                                                         $set('price', $price);
    //                                                         $set('total_price', $price);
                                                            
    //                                                     })
    //                                                     ->columnSpan(6),
    //                                                 TextInput::make('price')
    //                                                     ->label('Цена')
    //                                                     ->disabled()
    //                                                     ->numeric()
    //                                                     ->columnSpan(6),
    //                                             ])
    //                                             ->columns(12)
    //                                             ->columnSpan(12),
    //                                             Placeholder::make('total_sum')
    //                                                 ->label('Общая стоимость (всего)')
    //                                                 ->content(function (Get $get) {
    //                                                     $items = $get('labTestHistories') ?? [];
    //                                                     $total = collect($items)->sum('price');
    //                                                     return number_format($total, 2, '.', ' ') . ' сум';
    //                                                 })
    //                                                 ->columnSpanFull(), 
    //                             ])->columnSpan(8),
    //                     ]),
    //                 Wizard\Step::make('Лечение')
    //                     ->schema([
    //                         Section::make('Процедуры')
    //                             ->schema([
    //                                         Repeater::make('assignedProcedures')
    //                                             ->label('')
    //                                             ->defaultItems(2)
    //                                             ->relationship() // Agar relationship bor bo‘lsa
    //                                             ->schema([
    //                                                 Select::make('procedure_id')
    //                                                     ->label('Тип процедура')
    //                                                     ->options(Procedure::all()->pluck('name', 'id'))
    //                                                     ->searchable()
    //                                                     ->required()
    //                                                     ->reactive()
    //                                                     ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
    //                                                         $price = Procedure::find($state)?->price_per_day ?? 0;
    //                                                         $set('price', $price);
    //                                                         $set('total_price', $price * ($get('sessions') ?? 1));
                                                            
    //                                                         static::recalculateTotalSum($get, $set);
    //                                                     })
    //                                                     ->columnSpan(4),

    //                                                 TextInput::make('price')
    //                                                     ->label('Цена')
    //                                                     ->readOnly()
    //                                                     ->numeric()
    //                                                     ->columnSpan(3),

    //                                                 TextInput::make('sessions')
    //                                                     ->label('Кол сеансов')
    //                                                     ->numeric()
    //                                                     ->default(1)
    //                                                     ->required()
    //                                                     ->reactive()
    //                                                     ->afterStateUpdated(function (Get $get, Set $set, $state) {
    //                                                         $set('total_price', ($get('price') ?? 0) * ($state ?? 1));
                                                            
    //                                                         static::recalculateTotalSum($get, $set);
    //                                                     })
    //                                                     ->columnSpan(2),

    //                                                 TextInput::make('total_price')
    //                                                     ->label('Общая стоимость')
    //                                                     ->disabled()
    //                                                     ->numeric()
    //                                                     ->columnSpan(3)
    //                                                     ->afterStateUpdated(function (Get $get, Set $set) {
    //                                                         static::recalculateTotalSum($get, $set);
    //                                                     }),
    //                                             ])
    //                                             ->afterStateHydrated(function (Get $get, Set $set, $state) {
    //                                                 foreach ($state as $index => $item) {
    //                                                     $price = $item['price'] ?? 0;
    //                                                     $sessions = $item['sessions'] ?? 1;
    //                                                     $total = $price * $sessions;
    //                                                     $set("assignedProcedures.{$index}.total_price", $total);
    //                                                 }
    //                                             })
    //                                             ->columns(12)->columnSpan(12),
    //                                             Placeholder::make('total_sum')
    //                                                 ->label('Общая стоимость (всего)')
    //                                                 ->content(function (Get $get) {
    //                                                     $items = $get('assignedProcedures') ?? [];
    //                                                     $total = collect($items)->sum('total_price');
    //                                                     return number_format($total, 2, '.', ' ') . ' сум';
    //                                                 })
    //                                                 ->columnSpanFull(), 
    //                             ])->columns(12)->columnSpan(12),
    //                         // Eng oddiy va tushunarli variant
    //                         Fieldset::make('Койка') 
    //                             ->relationship('medicalBed') 
    //                             ->schema([ 
    //                                 Select::make('tariff_id') 
    //                                     ->label('Тарифф') 
    //                                     ->options(function () { 
    //                                         return Tariff::all()->mapWithKeys(function ($tariff) { 
    //                                             return [$tariff->id => $tariff->name . ' - ' . number_format($tariff->daily_price, 0) . ' сум']; 
    //                                         }); 
    //                                     }) 
    //                                     ->reactive() 
    //                                     ->required()
    //                                     ->columnSpan(4), 

    //                                 Select::make('ward_id') 
    //                                     ->label('Палата') 
    //                                     ->options(function (Get $get) { 
    //                                         $tariffId = $get('tariff_id'); 
    //                                         if (!$tariffId) return []; 
                                            
    //                                         return Ward::where('tariff_id', $tariffId)
    //                                             ->get()
    //                                             ->mapWithKeys(function ($ward) {
    //                                                 // Bo'sh koygalar sonini hisoblash
                                                        
    //                                                 return [$ward->id => $ward->name . " ({$ward->availableBedsCount} на пустой койке)"];
    //                                             });
    //                                     }) 
    //                                     ->reactive() 
    //                                     ->required()
    //                                     ->visible(fn (Get $get) => filled($get('tariff_id'))) 
    //                                     ->columnSpan(4), 

    //                                 Select::make('bed_id') 
    //                                     ->label('На пустой койке') 
    //                                     ->options(function (Get $get) { 
    //                                         $wardId = $get('ward_id'); 
    //                                         if (!$wardId) return []; 
                                            
    //                                         return Bed::where('ward_id', $wardId)
    //                                             ->availableBeds()
    //                                             ->get()
    //                                             ->mapWithKeys(function ($bed) {
    //                                                 return [$bed->id => "Койка #{$bed->number}"];
    //                                             });
    //                                     }) 
    //                                     ->required()
    //                                     ->visible(fn (Get $get) => filled($get('ward_id'))) 
    //                                     ->columnSpan(4), 
    //                             ])->columns(12)->columnSpan(12),
    //                         Fieldset::make('Питание')
    //                             ->relationship('medicalMeal')
    //                             ->schema([
    //                             Select::make('meal_type_id')
    //                                 ->label('Питание')
    //                                 ->options(function () {
    //                                     return MealType::all()
    //                                         ->mapWithKeys(function ($meal_type) {
    //                                             return [$meal_type->id => $meal_type->name . ' - ' . number_format($meal_type->daily_price, 0, '.', ' ') . ' сум/kun'];
    //                                         });
    //                                 })
    //                                 ->reactive()
    //                                 ->afterStateUpdated(fn (Set $set) => $set('ward_id', null))
    //                                 ->columnSpan(4),
    //                             Group::make()
    //                                     ->schema([
                                            
    //                                         Placeholder::make('meal_name')
    //                                                 ->label('Название')
    //                                                 ->visible(fn (Get $get) => filled($get('meal_type_id')))
    //                                                 ->content(fn (Get $get) => MealType::find($get('meal_type_id'))->name ?? '-')->columnSpan(6),
    //                                         Placeholder::make('meal_daily_price')
    //                                                 ->label('Цена')
    //                                                 ->visible(fn (Get $get) => filled($get('meal_type_id')))
    //                                                 ->content(fn (Get $get) => MealType::find($get('meal_type_id'))->daily_price ?? '-')->columnSpan(6),
    //                                         Placeholder::make('meal_description')
    //                                                 ->label('Описание')
    //                                                 ->visible(fn (Get $get) => filled($get('meal_type_id')))
    //                                                 ->content(fn (Get $get) => MealType::find($get('meal_type_id'))->description ?? '-')->columnSpan(12),
    //                                 ])->columns(12)->columnSpan(8),
    //                             ])->columns(12)->columnSpan(12),
    //                         Section::make()
    //                             ->schema([
    //                                 DatePicker::make('admission_date')
    //                                     ->label('Дата поступления')
    //                                     ->default(Carbon::now())
    //                                     ->columnSpan(6),
    //                                 DatePicker::make('discharge_date')
    //                                     ->label('Дата выписки')
    //                                     ->columnSpan(6),  
    //                             ])->columns(12)->columnSpan(12),
    //                     ]),
                        
    //                 Wizard\Step::make('Итого расчет')
    //                     ->schema([
    //                         Section::make('Детализация стоимости')
    //                             ->schema([
    //                                 // Proceduralar uchun hisob
    //                                 Placeholder::make('procedures_total')
    //                                     ->label('Стоимость процедур')
    //                                     ->content(function (Get $get) {
    //                                         $items = $get('assignedProcedures') ?? [];
    //                                         $total = collect($items)->sum('total_price');
    //                                         return number_format($total, 0, '.', ' ') . ' сум';
    //                                     })
    //                                     ->columnSpan(6),
    //                                 Placeholder::make('lab_test_total')
    //                                     ->label('Стоимость анализ')
    //                                     ->content(function (Get $get) {
    //                                         $items = $get('labTestHistories') ?? [];
    //                                         $total = collect($items)->sum('price');
    //                                         return number_format($total, 0, '.', ' ') . ' сум';
    //                                     })
    //                                     ->columnSpan(6),

    //                                 // Koyka uchun hisob
    //                                 Placeholder::make('bed_total')
    //                                     ->label('Стоимость койки')
    //                                     ->content(function (Get $get) {
    //                                         $bedId = $get('medicalBed.bed_id');
    //                                         $admissionDate = $get('admission_date');
    //                                         $dischargeDate = $get('discharge_date');

    //                                         if (!$bedId || !$admissionDate || !$dischargeDate) {
    //                                             return '0 сум (не выбрано)';
    //                                         }

    //                                         $bed = \App\Models\Bed::with('ward.tariff')->find($bedId);
    //                                         if (!$bed) {
    //                                             return '0 сум (койка не найдена)';
    //                                         }

    //                                         $dailyPrice = $bed->ward->tariff->daily_price;
    //                                         $days = \Carbon\Carbon::parse($admissionDate)->diffInDays(\Carbon\Carbon::parse($dischargeDate));
    //                                         $total = $dailyPrice * $days;

    //                                         return number_format($total, 0, '.', ' ') . ' сум (' . $days . ' дней × ' . number_format($dailyPrice, 0, '.', ' ') . ')';
    //                                     })
    //                                     ->columnSpan(6),

    //                                 // Ovqatlanish uchun hisob
    //                                 Placeholder::make('meal_total')
    //                                     ->label('Стоимость питания')
    //                                     ->content(function (Get $get) {
    //                                         $mealTypeId = $get('medicalMeal.meal_type_id');
    //                                         $admissionDate = $get('admission_date');
    //                                         $dischargeDate = $get('discharge_date');

    //                                         if (!$mealTypeId || !$admissionDate || !$dischargeDate) {
    //                                             return '0 сум (не выбрано)';
    //                                         }

    //                                         $mealType = \App\Models\MealType::find($mealTypeId);
    //                                         if (!$mealType) {
    //                                             return '0 сум (питание не найдено)';
    //                                         }

    //                                         $dailyPrice = $mealType->daily_price;
    //                                         $days = \Carbon\Carbon::parse($admissionDate)->diffInDays(\Carbon\Carbon::parse($dischargeDate));
    //                                         $total = $dailyPrice * $days;

    //                                         return number_format($total, 0, '.', ' ') . ' сум (' . $days . ' дней × ' . number_format($dailyPrice, 0, '.', ' ') . ')';
    //                                     })
    //                                     ->columnSpan(6),

    //                                 // Umumiy summa
    //                                 Placeholder::make('grand_total')
    //                                     ->label('ОБЩАЯ СТОИМОСТЬ')
    //                                     ->content(function (Get $get) {
    //                                         // Proceduralar
    //                                         $proceduresItems = $get('assignedProcedures') ?? [];
    //                                         $proceduresTotal = collect($proceduresItems)->sum('total_price');
    //                                         //Analizlar
    //                                         $labTestItems = $get('labTestHistories') ?? [];
    //                                         $labTestTotal = collect($labTestItems)->sum('price');

    //                                         // Koyka
    //                                         $bedTotal = 0;
    //                                         $bedId = $get('medicalBed.bed_id');
    //                                         $admissionDate = $get('admission_date');
    //                                         $dischargeDate = $get('discharge_date');

    //                                         if ($bedId && $admissionDate && $dischargeDate) {
    //                                             $bed = \App\Models\Bed::with('ward.tariff')->find($bedId);
    //                                             if ($bed) {
    //                                                 $days = \Carbon\Carbon::parse($admissionDate)->diffInDays(\Carbon\Carbon::parse($dischargeDate));
    //                                                 $bedTotal = $bed->ward->tariff->daily_price * $days;
    //                                             }
    //                                         }

    //                                         // Ovqatlanish
    //                                         $mealTotal = 0;
    //                                         $mealTypeId = $get('medicalMeal.meal_type_id');

    //                                         if ($mealTypeId && $admissionDate && $dischargeDate) {
    //                                             $mealType = \App\Models\MealType::find($mealTypeId);
    //                                             if ($mealType) {
    //                                                 $days = \Carbon\Carbon::parse($admissionDate)->diffInDays(\Carbon\Carbon::parse($dischargeDate));
    //                                                 $mealTotal = $mealType->daily_price * $days;
    //                                             }
    //                                         }

    //                                         $grandTotal = $proceduresTotal + $labTestTotal + $bedTotal + $mealTotal;

    //                                         return new \Illuminate\Support\HtmlString("
    //                                             <div class='bg-blue-50 p-4 rounded-lg border-2 border-blue-200'>
    //                                                 <div class='text-2xl font-bold text-blue-800'>
    //                                                     " . number_format($grandTotal, 0, '.', ' ') . " сум
    //                                                 </div>
    //                                             </div>
    //                                         ");
    //                                     })
    //                                     ->columnSpan(6),
    //                             ])
    //                             ->columns(12)
    //                             ->columnSpan(12),

    //                         // Batafsil ma'lumotlar
    //                         Section::make('Подробная информация')
    //                             ->schema([
    //                                 Placeholder::make('detailed_info')
    //                                     ->label('')
    //                                     ->content(function (Get $get) {
    //                                         $patient = \App\Models\Patient::find($get('patient_id'));
    //                                         $html = '<div class="space-y-4">';

    //                                         // Пациент ma'lumotlari
    //                                         $html .= '<div class="bg-gray-50 p-3 rounded">';
    //                                         $html .= '<h4 class="font-semibold text-gray-700 mb-2">Информация о пациенте:</h4>';
    //                                         $html .= '<div>ФИО: ' . ($patient->full_name ?? '-') . '</div>';
    //                                         $html .= '<div>Дата поступления: ' . ($get('admission_date') ? \Carbon\Carbon::parse($get('admission_date'))->format('d.m.Y') : '-') . '</div>';
    //                                         $html .= '<div>Дата выписки: ' . ($get('discharge_date') ? \Carbon\Carbon::parse($get('discharge_date'))->format('d.m.Y') : '-') . '</div>';
                                            
    //                                         if ($get('admission_date') && $get('discharge_date')) {
    //                                             $days = \Carbon\Carbon::parse($get('admission_date'))->diffInDays(\Carbon\Carbon::parse($get('discharge_date')));
    //                                             $html .= '<div class="font-semibold">Общее количество дней: ' . $days . '</div>';
    //                                         }
    //                                         $html .= '</div>';

    //                                         // Koyka ma'lumotlari
    //                                         if ($get('bed_id')) {
    //                                             $bed = \App\Models\Bed::with('ward.tariff')->find($get('bed_id'));
    //                                             if ($bed) {
    //                                                 $html .= '<div class="bg-green-50 p-3 rounded">';
    //                                                 $html .= '<h4 class="font-semibold text-green-700 mb-2">Информация о койке:</h4>';
    //                                                 $html .= '<div>Койка: #' . $bed->number . '</div>';
    //                                                 $html .= '<div>Палата: ' . $bed->ward->name . '</div>';
    //                                                 $html .= '<div>Тариф: ' . $bed->ward->tariff->name . '</div>';
    //                                                 $html .= '<div>Цена за день: ' . number_format($bed->ward->tariff->daily_price, 0, '.', ' ') . ' сум</div>';
    //                                                 $html .= '</div>';
    //                                             }
    //                                         }

    //                                         // Ovqatlanish ma'lumotlari
    //                                         if ($get('meal_type_id')) {
    //                                             $mealType = \App\Models\MealType::find($get('meal_type_id'));
    //                                             if ($mealType) {
    //                                                 $html .= '<div class="bg-yellow-50 p-3 rounded">';
    //                                                 $html .= '<h4 class="font-semibold text-yellow-700 mb-2">Информация о питании:</h4>';
    //                                                 $html .= '<div>Тип питания: ' . $mealType->name . '</div>';
    //                                                 $html .= '<div>Цена за день: ' . number_format($mealType->daily_price, 0, '.', ' ') . ' сум</div>';
    //                                                 if ($mealType->description) {
    //                                                     $html .= '<div>Описание: ' . $mealType->description . '</div>';
    //                                                 }
    //                                                 $html .= '</div>';
    //                                             }
    //                                         }

    //                                         $lab_tests = $get('labTestHistories') ?? [];
    //                                         if (!empty($lab_tests)) {
    //                                             $html .= '<div class="bg-blue-50 p-3 rounded">';
    //                                             $html .= '<h4 class="font-semibold text-blue-700 mb-2">Анализы:</h4>';
    //                                             foreach ($lab_tests as $index => $lab_test) {
    //                                                 if (!empty($lab_test['lab_test_id'])) {
    //                                                     $lab = \App\Models\LabTest::find($lab_test['lab_test_id']);
    //                                                     if ($lab) {
    //                                                         $html .= '<div class="mb-2 pl-4 border-l-2 border-blue-300">';
    //                                                         $html .= '<div class="font-medium">' . $lab->name . '</div>';
    //                                                         $html .= '<div class="text-sm text-gray-600">';
    //                                                         $html .= 'Цена: ' . number_format($lab_test['price'] ?? 0, 0, '.', ' ') . ' сум ';
    //                                                         $html .= '</div>';
    //                                                         $html .= '</div>';
    //                                                     }
    //                                                 }
    //                                             }
    //                                             $html .= '</div>';
    //                                         }
                                            
    //                                         // Proceduralar ro'yxati
    //                                         $procedures = $get('assignedProcedures') ?? [];
    //                                         if (!empty($procedures)) {
    //                                             $html .= '<div class="bg-blue-50 p-3 rounded">';
    //                                             $html .= '<h4 class="font-semibold text-blue-700 mb-2">Назначенные процедуры:</h4>';
    //                                             foreach ($procedures as $index => $procedure) {
    //                                                 if (!empty($procedure['procedure_id'])) {
    //                                                     $proc = \App\Models\Procedure::find($procedure['procedure_id']);
    //                                                     if ($proc) {
    //                                                         $html .= '<div class="mb-2 pl-4 border-l-2 border-blue-300">';
    //                                                         $html .= '<div class="font-medium">' . $proc->name . '</div>';
    //                                                         $html .= '<div class="text-sm text-gray-600">';
    //                                                         $html .= 'Цена: ' . number_format($procedure['price'] ?? 0, 0, '.', ' ') . ' сум × ';
    //                                                         $html .= ($procedure['sessions'] ?? 1) . ' сеансов = ';
    //                                                         $html .= number_format($procedure['total_price'] ?? 0, 0, '.', ' ') . ' сум';
    //                                                         $html .= '</div>';
    //                                                         $html .= '</div>';
    //                                                     }
    //                                                 }
    //                                             }
    //                                             $html .= '</div>';
    //                                         }

    //                                         $html .= '</div>';

    //                                         return new \Illuminate\Support\HtmlString($html);
    //                                     })
    //                                     ->columnSpanFull(),
    //                             ])
    //                             ->columnSpan(12),
    //                 ])])->columnSpan(2),
    //         ]);
    // }
    public static function form(Form $form): Form{
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('patient_id')
                            ->label('Пациент')
                            ->disabled()
                            ->relationship('patient', 'full_name') // yoki kerakli atribut
                            ->default(request()->get('patient_id'))
                            ->required(),
                        Hidden::make('doctor_id')
                            ->default(fn () => auth()->user()->id)
                            ->dehydrated(true),
                        Textarea::make('diagnosis')
                            ->label('Диагноз'),
                        Textarea::make('complaints')
                            ->label('Жалобы'),
                        Textarea::make('history')
                            ->label('Анамнез'),
                        Textarea::make('objectively')
                            ->label('Объективно'),
                        Textarea::make('treatment')
                            ->label('Лечение'),
                        FileUpload::make('photo')
                                ->label('Фото')
                                ->image()
                                ->disk('public') 
                                ->directory('osmotr')
                                ->imageEditor()
                                ->imageEditorAspectRatios([
                                    '16:9',
                                    '4:3',
                                    '1:1',
                                ]),
                    ])
            ]);
    }
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static function recalculateTotalSum(Get $get, Set $set): void
    {
        $items = $get('assignedProcedures') ?? [];
        $total = collect($items)->sum('total_price');
        $set('total_sum', $total);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('id')
                //     ->label('№')
                //     ->sortable(),
                TextColumn::make('patient.full_name')
                    ->label('ФИО')
                    ->sortable(),
                TextColumn::make('calculateTotalCost')
                    ->label('Обшый сумма')
                    ->getStateUsing(function ($record) {
                        return number_format($record->total_cost,0,'.',' ').' сум';
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                ->label('To\'lov holati')
                ->state(function ($record) {
                    $totalCost = $record->getTotalCost();
                    $totalPaid = $record->getTotalPaidAmount();
                    $remaining = $totalCost - $totalPaid;
                    
                    if ($remaining <= 0) {
                        return 'To\'liq to\'langan';
                    } elseif ($totalPaid > 0) {
                        return 'Qisman to\'langan';
                    } else {
                        return 'To\'lanmagan';
                    }
                })
                ->badge()
                ->color(function ($record) {
                    $totalCost = $record->getTotalCost();
                    $totalPaid = $record->getTotalPaidAmount();
                    $remaining = $totalCost - $totalPaid;
                    
                    if ($remaining <= 0) {
                        return 'success';
                    } elseif ($totalPaid > 0) {
                        return 'warning';
                    } else {
                        return 'danger';
                    }
                }),
                TextColumn::make('created_at')
                    ->label('')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            // ->actions([
            //     Tables\Actions\EditAction::make(),
            // ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Action::make('return_procedures')
                    ->label('Proseduralarni qaytarish')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->form([
                        Select::make('procedure_selections')
                            ->label('Qaytariladigan proseduralar')
                            ->multiple()
                            ->options(function ($record) {
                                return $record->assignedProcedures()
                                    ->with('procedure')
                                    ->get()
                                    ->mapWithKeys(function ($assignedProcedure) {
                                        return [
                                            $assignedProcedure->id => 
                                                $assignedProcedure->procedure->name . 
                                                ' (Sessions: ' . $assignedProcedure->sessions . 
                                                ', Price: $' . $assignedProcedure->price . ')'
                                        ];
                                    });
                            })
                            ->required()
                            ->searchable(),
                            
                        Forms\Components\Repeater::make('procedure_returns')
                            ->label('Qaytariladigan proseduralar tafsiloti')
                            ->schema([
                                Select::make('assigned_procedure_id')
                                    ->label('Prosedura')
                                    ->options(function ($get, $record) {
                                        return $record->assignedProcedures()
                                            ->with('procedure')
                                            ->get()
                                            ->mapWithKeys(function ($assignedProcedure) {
                                                return [
                                                    $assignedProcedure->id => 
                                                        $assignedProcedure->procedure->name . 
                                                        ' (Max sessions: ' . $assignedProcedure->sessions . ')'
                                                ];
                                            });
                                    })
                                    ->required()
                                    ->reactive(),
                                    
                                TextInput::make('returned_sessions')
                                    ->label('Qaytariladigan seanslar soni')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->rules([
                                        function ($get, $record) {
                                            return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                                $assignedProcedureId = $get('assigned_procedure_id');
                                                if ($assignedProcedureId) {
                                                    $assignedProcedure = $record->assignedProcedures()
                                                        ->find($assignedProcedureId);
                                                    
                                                    if ($assignedProcedure && $value > $assignedProcedure->sessions) {
                                                        $fail("Qaytariladigan seanslar soni {$assignedProcedure->sessions} dan oshmasligi kerak.");
                                                    }
                                                }
                                            };
                                        },
                                    ]),
                            ])
                            ->minItems(1)
                            ->addActionLabel('Prosedura qo\'shish')
                            ->collapsible(),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            foreach ($data['procedure_returns'] as $return) {
                                $assignedProcedure = AssignedProcedure::find($return['assigned_procedure_id']);
                                
                                if (!$assignedProcedure) {
                                    continue;
                                }
                                
                                // Qaytarilgan prosedura yaratish
                                ReturnedProcedure::create([
                                    'medical_history_id' => $record->id,
                                    'procedure_id' => $assignedProcedure->procedure_id,
                                    'sessions' => $return['returned_sessions'],
                                ]);
                                
                                // Assigned procedure dan seanslarni ayirish
                                $assignedProcedure->sessions -= $return['returned_sessions'];
                                
                                // Agar barcha seanslar qaytarilgan bo'lsa, assigned procedure ni o'chirish
                                if ($assignedProcedure->sessions <= 0) {
                                    $assignedProcedure->delete();
                                } else {
                                    $assignedProcedure->save();
                                }
                            }
                            
                            Notification::make()
                                ->title('Muvaffaqiyatli!')
                                ->body('Proseduralar muvaffaqiyatli qaytarildi.')
                                ->success()
                                ->send();
                                
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Xatolik!')
                                ->body('Proseduralarni qaytarishda xatolik yuz berdi: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(function ($record) {
                        // Faqat assigned procedures mavjud bo'lsa ko'rsatish
                        return $record->assignedProcedures()->exists();
                    }),
                ActionGroup::make([
                    // boshqa actionlar...
                    
                    Action::make('add_payment')
                        ->label('To\'lov qo\'shish')
                        ->icon('heroicon-o-credit-card')
                        ->color('success')
                        ->modalWidth(MaxWidth::TwoExtraLarge)
                        ->form([
                            Section::make('To\'lov ma\'lumotlari')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('total_cost')
                                                ->label('Jami xarajat')
                                                ->disabled()
                                                ->default(function ($record) {
                                                    return number_format($record->getTotalCost(), 0, '.', ' ') . ' сум';
                                                }),
                                                
                                            TextInput::make('total_paid')
                                                ->label('To\'langan')
                                                ->disabled()
                                                ->default(function ($record) {
                                                    return number_format($record->getTotalPaidAmount(), 0, '.', ' ') . ' сум';
                                                }),
                                        ]),
                                        
                                    TextInput::make('remaining')
                                        ->label('Qoldiq')
                                        ->disabled()
                                        ->default(function ($record) {
                                            $remaining = $record->getTotalCost() - $record->getTotalPaidAmount();
                                            return number_format($remaining, 0, '.', ' ') . ' сум';
                                        }),
                                ]),
                                
                            Section::make('Yangi to\'lov')
                                ->schema([
                                    TextInput::make('amount')
                                        ->label('To\'lov miqdori')
                                        ->numeric()
                                        ->required()
                                        ->minValue(0.01)
                                        ->step(0.01)
                                        ->suffix('сум')
                                        ->placeholder('0.00')
                                        ->live()
                                        ->afterStateUpdated(function ($state, $set, $record) {
                                            $remaining = $record->getTotalCost() - $record->getTotalPaidAmount();
                                            if ($state > $remaining) {
                                                $set('amount', $remaining);
                                            }
                                        }),
                                    Select::make('payment_type_id')
                                        ->label('Тип оплаты')
                                        ->options(PaymentType::all()->pluck('name', 'id'))
                                        ->required(),
                                    DatePicker::make('payment_date')
                                        ->label('To\'lov sanasi')
                                        ->default(now())
                                        ->required()
                                        ->native(false),
                                        
                                    Textarea::make('description')
                                        ->label('Izoh')
                                        ->placeholder('To\'lov haqida qo\'shimcha ma\'lumot')
                                        ->maxLength(255)
                                        ->rows(3),
                                ]),
                        ])
                        ->action(function (array $data, $record) {
                            // To'lovni saqlash
                            \App\Models\Payment::create([
                                'patient_id' => $record->patient_id,
                                'medical_history_id' => $record->id,
                                'amount' => $data['amount'],
                                'payment_type_id' => $data['payment_type_id'],
                                'description' => $data['description'] ?? null,
                                'payment_date' => $data['payment_date'],
                            ]);

                            // Muvaffaqiyat xabari
                            Notification::make()
                                ->title('To\'lov muvaffaqiyatli qo\'shildi!')
                                ->success()
                                ->body("Miqdor: " . number_format($data['amount'], 2) . " сум")
                                ->send();
                        })
                        ->modalHeading('To\'lov qo\'shish')
                        ->modalSubmitActionLabel('To\'lovni saqlash')
                        ->modalCancelActionLabel('Bekor qilish'),
                        
                    Action::make('view_payments')
                        ->label('To\'lovlar tarixi')
                        ->icon('heroicon-o-banknotes')
                        ->color('info')
                        ->modalWidth(MaxWidth::FourExtraLarge)
                        ->modalContent(function ($record) {
                            $payments = \App\Models\Payment::where('medical_history_id', $record->id)
                                ->orderBy('payment_date', 'desc')
                                ->get();
                                
                            $totalPaid = $payments->sum('amount');
                            $totalCost = $record->getTotalCost();
                            $remaining = $totalCost - $totalPaid;
                            
                            return view('filament.modals.payments-history', [
                                'payments' => $payments,
                                'totalPaid' => $totalPaid,
                                'totalCost' => $totalCost,
                                'remaining' => $remaining,
                            ]);
                        })
                        ->modalHeading('To\'lovlar tarixi')
                        ->modalCancelActionLabel('Yopish'),
                        
                    Action::make('payment_receipt')
                        ->label('Kvitansiya')
                        ->icon('heroicon-o-document-text')
                        ->color('gray')
                        ->visible(fn ($record) => $record->getTotalPaidAmount() > 0)
                        ->url(fn ($record) => route('payment.receipt', $record->id))
                        ->openUrlInNewTab(),
                        
                ])->label('To\'lovlar')
                ->icon('heroicon-o-currency-dollar')
                ->button()
                ->outlined(),
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

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccommodationResource\Pages;
use App\Filament\Resources\AccommodationResource\RelationManagers;
use App\Models\Accommodation;
use App\Models\Bed;
use App\Models\MealType;
use App\Models\MedicalHistory;
use App\Models\Tariff;
use App\Models\Ward;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
                        Select::make('medical_history_id')
                            ->label('История болезно')
                            ->required()
                            ->options(function (Get $get) {
                                $patientId = $get('patient_id');

                                return \App\Models\MedicalHistory::where('patient_id', $patientId)
                                    // ->doesntHave('medicalInspection') // agar faqat bog‘lanmaganlar kerak bo‘lsa
                                    ->get()
                                    ->mapWithKeys(function ($history) {
                                        $formattedId = str_pad('№'.$history->id, 10);
                                        $formattedDate = \Carbon\Carbon::parse($history->created_at)->format('d.m.Y H:i');
                                        return [$history->id => $formattedId . ' - ' . $formattedDate];
                                    });
                            })
                            ->required()
                            ->columnSpan(12),
                            Hidden::make('created_id')
                                ->default(fn () => auth()->user()->id)
                                ->dehydrated(true),
                            DateTimePicker::make('admission_date')
                                ->label('Дата поступления')
                                ->reactive()
                                ->default(Carbon::now())
                                ->columnSpan(6),
                            DatePicker::make('discharge_date')
                                ->label('Дата выписки')
                                ->reactive()
                                ->columnSpan(6),  
                        ])->columns(12)->columnSpan(12),
                        Section::make('Койка') 
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
                                        ->columnSpan(4), 

                                    Select::make('ward_id') 
                                        ->label('Палата') 
                                        ->options(function (Get $get) { 
                                            $tariffId = $get('tariff_id'); 
                                            if (!$tariffId) return []; 
                                            
                                            return Ward::where('tariff_id', $tariffId)
                                                ->get()
                                                ->mapWithKeys(function ($ward) {
                                                    // Bo'sh koygalar sonini hisoblash
                                                        
                                                    return [$ward->id => $ward->name . " ({$ward->availableBedsCount} на пустой койке)"];
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
                                            if (!$wardId) return []; 
                                            
                                            return Bed::where('ward_id', $wardId)
                                                ->availableBeds()
                                                ->get()
                                                ->mapWithKeys(function ($bed) {
                                                    return [$bed->id => "Койка #{$bed->number}"];
                                                });
                                        }) 
                                        ->reactive() 
                                        ->required()
                                        ->visible(fn (Get $get) => filled($get('ward_id'))) 
                                        ->columnSpan(4), 
                                ])->columns(12)->columnSpan(12),
                            Section::make('Питание')
                                ->schema([
                                Select::make('meal_type_id')
                                    ->label('Питание')
                                    ->options(function () {
                                        return MealType::all()
                                            ->mapWithKeys(function ($meal_type) {
                                                return [$meal_type->id => $meal_type->name . ' - ' . number_format($meal_type->daily_price, 0, '.', ' ') . ' сум/kun'];
                                            });
                                    })
                                    ->reactive()
                                    ->columnSpan(4),
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

                                            $grandTotal =$bedTotal + $mealTotal;

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
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnedAccommodationResource\Pages;
use App\Filament\Resources\ReturnedAccommodationResource\RelationManagers;
use App\Models\Accommodation;
use App\Models\ReturnedAccommodation;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReturnedAccommodationResource extends Resource
{
    protected static ?string $model = ReturnedAccommodation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Hidden::make('patient_id')
    //                 ->default(fn () => Accommodation::find(request()->get('accommodation_id'))->patient_id)
    //                 ->dehydrated(true),
    //             Hidden::make('medical_history_id')
    //                 ->default(fn () => Accommodation::find(request()->get('accommodation_id'))->medical_history_id)
    //                 ->dehydrated(true),
    //             Hidden::make('accommodation_id')
    //                 ->default(fn () => request()->get('accommodation_id'))
    //                 ->dehydrated(true),
    //         ]);
    // }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Forms\Components\Section::make('Qaytarish Ma\'lumotlari')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Hidden::make('created_id')
                                            ->default(fn () => auth()->user()->id)
                                            ->dehydrated(true),
                                        Forms\Components\Hidden::make('patient_id')
                                            ->default(function () {
                                                $accommodationId = request()->get('accommodation_id');
                                                if ($accommodationId) {
                                                    $accommodation = Accommodation::find($accommodationId);
                                                    return $accommodation?->patient_id;
                                                }
                                                return null;
                                            }),

                                        Forms\Components\Hidden::make('medical_history_id')
                                            ->default(function () {
                                                $accommodationId = request()->get('accommodation_id');
                                                if ($accommodationId) {
                                                    $accommodation = Accommodation::find($accommodationId);
                                                    return $accommodation?->medical_history_id;
                                                }
                                                return null;
                                            }),

                                        Hidden::make('created_id')
                                            ->default(fn () => auth()->user()->id)
                                            ->dehydrated(true),
                                        Forms\Components\Hidden::make('accommodation_id')
                                            ->default(request()->get('accommodation_id')),

                                        // Discharge date
                                        Forms\Components\DatePicker::make('discharge_date')
                                            ->label('Дата выпуска')
                                            ->required()
                                            ->default(now())
                                            ->minDate(now()->startOfDay())
                                            ->maxDate(function () {
                                                $accommodationId = request()->get('accommodation_id');
                                                if ($accommodationId) {
                                                    $accommodation = Accommodation::find($accommodationId);
                                                    return $accommodation?->discharge_date;
                                                }
                                                return null;
                                            }) // Allow discharge date within the last 30 days
                                            ->displayFormat('d.m.Y')
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('comment')
                                            ->label('Комментарий')
                                            ->columnSpanFull(),
                                    ]),
                            ])->columnSpan(6),
                            
                        Forms\Components\Section::make('Accommodation Ma\'lumotlari')
                            ->description('Qaytarilayotgan accommodation haqida ma\'lumotlar')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        // Accommodation selection (hidden in create, visible in edit)
                                        Forms\Components\Select::make('accommodation_id')
                                            ->label('Accommodation')
                                            ->relationship('accommodation', 'id')
                                            ->getOptionLabelFromRecordUsing(fn ($record) => "#{$record->id} - {$record->patient->name} {$record->patient->surname}")
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                if ($state) {
                                                    $accommodation = Accommodation::with([
                                                        'patient', 'ward', 'bed', 'tariff', 
                                                        'mealType', 'statusPayment', 'medicalHistory'
                                                    ])->find($state);
                                                    
                                                    if ($accommodation) {
                                                        $set('patient_id', $accommodation->patient_id);
                                                        $set('medical_history_id', $accommodation->medical_history_id);
                                                    }
                                                }
                                            })
                                            ->hidden(fn ($livewire) => $livewire instanceof Pages\CreateReturnedAccommodation && request()->has('accommodation_id')),

                                        // Placeholder ma'lumotlar
                                        Forms\Components\Placeholder::make('patient_info')
                                            ->label('Пациент')
                                            ->content(function ($record, Get $get) {
                                                $accommodationId = $get('accommodation_id') ?? request()->get('accommodation_id');
                                                if ($accommodationId) {
                                                    $accommodation = Accommodation::with('patient')->find($accommodationId);
                                                    return $accommodation ? "{$accommodation->patient->full_name} " : 'Информация не найдена';
                                                }
                                                return $record?->patient ? "{$record->patient->full_name} " : 'Информация не найдена';
                                            }),

                                        Forms\Components\Placeholder::make('patient_phone')
                                            ->label('Телефон')
                                            ->content(function ($record, Get $get) {
                                                $accommodationId = $get('accommodation_id') ?? request()->get('accommodation_id');
                                                if ($accommodationId) {
                                                    $accommodation = Accommodation::with('patient')->find($accommodationId);
                                                    return $accommodation?->patient?->phone ?? 'Телефон не добавлен';
                                                }
                                                return $record?->patient?->phone ?? 'Телефон не добавлен';
                                            }),

                                        Forms\Components\Placeholder::make('ward_info')
                                            ->label('Койка')
                                            ->content(function ($record, Get $get) {
                                                $accommodationId = $get('accommodation_id') ?? request()->get('accommodation_id');
                                                if ($accommodationId) {
                                                    $accommodation = Accommodation::with(['ward','bed','tariff'])->find($accommodationId);
                                                    $ward_name=$accommodation?->ward?->name ?? 'Palata ma\'lumoti yo\'q';
                                                    $bed_name= $accommodation?->bed?->number ?? 'Karavot ma\'lumoti yo\'q';
                                                    $tariff_name= $accommodation ? "{$accommodation->tariff->name} - " . number_format($accommodation->tariff->daily_price) . " сум" : 'Tarif ma\'lumoti yo\'q';
                                                    return "{$ward_name}, {$bed_name}, {$tariff_name}";
                                                }
                                                return $record?->accommodation?->ward?->name ?? 'Нет информации о палате';
                                            }),
                                        Forms\Components\Placeholder::make('meal_type_info')
                                            ->label('Питание')
                                            ->content(function ($record, Get $get) {
                                                $accommodationId = $get('accommodation_id') ?? request()->get('accommodation_id');
                                                if ($accommodationId) {
                                                    $accommodation = Accommodation::with('mealType')->find($accommodationId);
                                                    return number_format($accommodation?->mealType?->daily_price ?? 0) . " сум - {$accommodation?->mealType?->name}";
                                                }
                                                return number_format($record?->accommodation?->mealType?->daily_price ?? 0) . " сум";
                                            }),

                                        Forms\Components\Placeholder::make('admission_date_info')
                                            ->label('Дата получения')
                                            ->content(function ($record, Get $get) {
                                                $accommodationId = $get('accommodation_id') ?? request()->get('accommodation_id');
                                                if ($accommodationId) {
                                                    $accommodation = Accommodation::find($accommodationId);
                                                    return $accommodation?->admission_date?? 'Дата не указана';
                                                }
                                                return $record?->accommodation?->admission_date?? 'Дата не указана';
                                            }),
                                        Forms\Components\Placeholder::make('discharge_date_info')
                                            ->label('Дата выпуска')
                                            ->content(function ($record, Get $get) {
                                                $accommodationId = $get('accommodation_id') ?? request()->get('accommodation_id');
                                                if ($accommodationId) {
                                                    $accommodation = Accommodation::find($accommodationId);
                                                    return $accommodation?->discharge_date?? 'Дата не указана';
                                                }
                                                return $record?->accommodation?->discharge_date?? 'Дата не указана';
                                            }),

                                        Forms\Components\Placeholder::make('status_payment_info')
                                            ->label('Статус платежа')
                                            ->content(function ($record, Get $get) {
                                                $accommodationId = $get('accommodation_id') ?? request()->get('accommodation_id');
                                                if ($accommodationId) {
                                                    $accommodation = Accommodation::with('statusPayment')->find($accommodationId);
                                                    return $accommodation?->statusPayment?->name ?? 'Статус платежа неизвестен';
                                                }
                                                return $record?->accommodation?->statusPayment?->name ?? 'Статус платежа неизвестен';
                                            }),
                                    ]),
                            ])->columnSpan(6),
                    ])->columns(12)->columnSpan(12)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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

    public static function shouldRegisterNavigation(): bool
    {
        return false;
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
            'index' => Pages\ListReturnedAccommodations::route('/'),
            'create' => Pages\CreateReturnedAccommodation::route('/create'),
            'edit' => Pages\EditReturnedAccommodation::route('/{record}/edit'),
        ];
    }
}

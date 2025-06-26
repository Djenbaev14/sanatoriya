<?php

namespace App\Filament\Resources\MedicalHistoryResource\Pages;

use App\Filament\Resources\MedicalHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;

class ViewMedicalHistory extends ViewRecord
{
    protected static string $resource = MedicalHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Tahrirlash')
                ->icon('heroicon-o-pencil'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        // Asosiy ma'lumotlar tab
                        Tabs\Tab::make("Основные данные")
                            ->icon('heroicon-o-user')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Section::make('Данные пациента')
                                            ->icon('heroicon-o-identification')
                                            ->schema([
                                                TextEntry::make('patient.full_name')
                                                    ->label('FIO')
                                                    ->weight(FontWeight::Bold),
                                                    
                                                TextEntry::make('patient.birth_date')
                                                    ->label('Tug\'ilgan sana')
                                                    ->date('d.m.Y')
                                                    ->placeholder('Kiritilmagan'),
                                                    
                                                TextEntry::make('patient.phone')
                                                    ->label('Telefon')
                                                    ->placeholder('Kiritilmagan'),
                                                    
                                                TextEntry::make('patient.address')
                                                    ->label('Manzil')
                                                    ->placeholder('Kiritilmagan'),
                                            ])
                                            ->columnSpan(1),
                                            
                                        Section::make('Медицинские показатели')
                                            ->icon('heroicon-o-heart')
                                            ->schema([
                                                TextEntry::make('height')
                                                    ->label('Bo\'yi (sm)')
                                                    ->suffix(' sm')
                                                    ->placeholder('O\'lchanmagan'),
                                                TextEntry::make('weight')
                                                    ->label('Vazni (kg)')
                                                    ->suffix(' kg')
                                                    ->placeholder('O\'lchanmagan'),
                                                    
                                                TextEntry::make('temperature')
                                                    ->label('Harorat (°C)')
                                                    ->suffix(' °C')
                                                    ->placeholder('O\'lchanmagan'),
                                                    
                                                TextEntry::make('disability_types')
                                                    ->label('Nogironlik turlari')
                                                    ->formatStateUsing(function ($state) {
                                                        if (empty($state)) {
                                                            return 'Yo\'q';
                                                        }
                                                        return is_array($state) ? implode(', ', $state) : $state;
                                                    })
                                                    ->placeholder('Yo\'q'),
                                                    
                                                TextEntry::make('side_effects')
                                                    ->label('Yon ta\'sirlar')
                                                    ->placeholder('Yo\'q')
                                                    ->columnSpanFull(),
                                            ])
                                            ->columnSpan(1),
                                    ]),
                                    
                                Grid::make(3)
                                    ->schema([
                                        Section::make('Дополнительные данные')
                                            ->icon('heroicon-o-information-circle')
                                            ->schema([
                                                TextEntry::make('is_emergency')
                                                    ->label('Shoshilinch holat')
                                                    ->formatStateUsing(fn($state) => $state ? 'Ha' : 'Yo\'q')
                                                    ->color(fn($state) => $state ? Color::Red : Color::Green)
                                                    ->weight(FontWeight::Bold),
                                                    
                                                TextEntry::make('transport_type')
                                                    ->label('Transport turi')
                                                    ->formatStateUsing(function ($state) {
                                                        return match($state) {
                                                            'ambulance' => 'Tez yordam',
                                                            'family' => 'Oila a\'zolari',
                                                            'self' => 'O\'zi',
                                                            'taxi' => 'Taksi',
                                                            'other' => 'Boshqa',
                                                            default => 'Kiritilmagan'
                                                        };
                                                    })
                                                    ->placeholder('Kiritilmagan'),
                                                    
                                                TextEntry::make('referred_from')
                                                    ->label('Qayerdan yuborilgan')
                                                    ->formatStateUsing(function ($state) {
                                                        return match($state) {
                                                            'clinic' => 'Poliklinika',
                                                            'hospital' => 'Kasalxona',
                                                            'emergency' => 'Tez yordam',
                                                            'self' => 'O\'zi murojaat',
                                                            'other' => 'Boshqa',
                                                            default => 'Kiritilmagan'
                                                        };
                                                    })
                                                    ->placeholder('Kiritilmagan'),
                                            ])
                                            ->columnSpan(2),
                                            
                                        Section::make('Изображение')
                                            ->schema([
                                                ImageEntry::make('photo')
                                                    ->label('')
                                                    ->height(200)
                                                    ->placeholder('Изображение не загружено'),
                                            ])
                                            ->columnSpan(1),
                                    ]),
                                    
                                Section::make('Системные данные')
                                    ->icon('heroicon-o-cog')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('createdBy.name')
                                                    ->label('Yaratgan foydalanuvchi')
                                                    ->placeholder('Noma\'lum'),
                                                    
                                                TextEntry::make('created_at')
                                                    ->label('Yaratilgan sana')
                                                    ->dateTime('d.m.Y H:i'),
                                                    
                                                TextEntry::make('updated_at')
                                                    ->label('O\'zgartirilgan sana')
                                                    ->dateTime('d.m.Y H:i'),
                                            ])
                                    ])
                                    ->collapsible()
                                    ->collapsed(),
                            ]),
                            
                        // Tibbiy ko'rik tab
                        Tabs\Tab::make('Медицинский осмотр')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->schema([
                                Section::make('')
                                    ->label('Tibbiy ko\'riklar')
                                    ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                    TextEntry::make('medicalInspection.id')
                                                        ->label('Скачать осмотр')
                                                        ->visible(fn ($record) => $record->medicalInspection !== null)
                                                        ->url(fn ($state) => route('download.inspection', $state))
                                                        ->openUrlInNewTab()
                                                        ->icon('heroicon-o-arrow-down-tray')
                                                        ->color(Color::Gray),
                                                        TextEntry::make('medicalInspection.initialDoctor.name')
                                                            ->label('Dastlabki shifokor')
                                                            ->weight(FontWeight::Bold)
                                                            ->color(Color::Blue)
                                                            ->placeholder('Tayinlanmagan'),
                                                            
                                                        TextEntry::make('medicalInspection.assignedDoctor.name')
                                                            ->label('Tayinlangan shifokor')
                                                            ->weight(FontWeight::Bold)
                                                            ->color(Color::Green)
                                                            ->placeholder('Tayinlanmagan'),
                                                    ]),
                                                    
                                                TextEntry::make('medicalInspection.admission_diagnosis')
                                                    ->label('Dastlabki diagnoz')
                                                    ->placeholder('Kiritilmagan')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('medicalInspection.complaints')
                                                    ->label('Shikoyatlar')
                                                    ->placeholder('Yo\'q')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('medicalInspection.medical_history')
                                                    ->label('Kasallik tarixi')
                                                    ->placeholder('Kiritilmagan')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('medicalInspection.objectively')
                                                    ->label('Ob\'ektiv ko\'rik')
                                                    ->placeholder('Kiritilmagan')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('medicalInspection.treatment')
                                                    ->label('Davolash')
                                                    ->placeholder('Tayinlanmagan')
                                                    ->columnSpanFull(),
                                                    
                                                Grid::make(2)
                                                    ->schema([
                                                        TextEntry::make('medicalInspection.created_at')
                                                            ->label('Yaratilgan')
                                                            ->dateTime('d.m.Y H:i'),
                                                            
                                                        TextEntry::make('medicalInspection.updated_at')
                                                            ->label('O\'zgartirilgan')
                                                            ->dateTime('d.m.Y H:i'),
                                                    ])
                                    ]),
                            ]),
                            
                        // Laboratoriya testlari tab
                        Tabs\Tab::make('Анализы')
                            ->icon('heroicon-o-beaker')
                            ->schema([
                                Section::make('')
                                    ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        TextEntry::make('labTestHistory.doctor.name')
                                                            ->label('Buyurgan shifokor')
                                                            ->weight(FontWeight::Bold)
                                                            ->color(Color::Blue)
                                                            ->placeholder('Noma\'lum'),
                                                            
                                                        TextEntry::make('labTestHistory.statusPayment.name')
                                                            ->label('To\'lov holati')
                                                            ->badge()
                                                            ->color(fn($state) => match($state) {
                                                                'To\'langan' => Color::Green,
                                                                'To\'lanmagan' => Color::Red,
                                                                'Qisman to\'langan' => Color::Orange,
                                                                default => Color::Gray
                                                            }),
                                                        TextEntry::make('labTestHistory.created_at')
                                                            ->label('Buyurilgan sana')
                                                            ->dateTime('d.m.Y H:i'),
                                                    ]),
                                                RepeatableEntry::make('labTestHistory.labTestDetails')
                                                        ->label('')
                                                        ->schema([
                                                            TextEntry::make('lab_test.name')->label('Название анализа'),
                                                            TextEntry::make('sessions')->label('Seanslar'),
                                                            TextEntry::make('price')->label('Narxi')->formatStateUsing(fn($state) => number_format($state, 0) . ' so‘m'),
                                                            TextEntry::make('result')->label('Natija')->placeholder('Yo‘q'),
                                                            TextEntry::make('created_at')->label('Yaratilgan')->dateTime('d.m.Y H:i'),
                                                        ])
                                                        ->columns(5)
                                                        ->default([]),
                                                                                                
                                                Grid::make(3)
                                                    ->schema([
                                                        TextEntry::make('labTestHistory.total_cost')
                                                            ->label('Umumiy summa')
                                                            ->color(Color::Blue)
                                                            ->badge()
                                                            ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . ' сум'),

                                                        TextEntry::make('labTestHistory.total_paid_amount')
                                                            ->label('To‘langan')
                                                            ->color(Color::Green)
                                                            ->badge()
                                                            ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . ' сум'),
                                                            
                                                        TextEntry::make('labTestHistory.total_debt_amount')
                                                            ->label('Qarzdorlik')
                                                            ->color(Color::Red)
                                                            ->badge()
                                                            ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . ' сум'),
                                                    ])
                                    ]),
                            ]),
                            
                        // Protseduralar tab
                        Tabs\Tab::make('Процедуры')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema([
                                Section::make()
                                    ->schema([
                                                    
                                                Grid::make(3)
                                                    ->schema([
                                                        TextEntry::make('assignedProcedure.created_at')
                                                            ->label('Buyurilgan sana')
                                                            ->dateTime('d.m.Y H:i'),
                                                        TextEntry::make('assignedProcedure.statusPayment.name')
                                                            ->label('To\'lov holati')
                                                            ->badge()
                                                            ->color(fn($state) => match($state) {
                                                                'To\'langan' => Color::Green,
                                                                'To\'lanmagan' => Color::Red,
                                                                'Qisman to\'langan' => Color::Orange,
                                                                default => Color::Gray
                                                            }),
                                                    ]),
                                                RepeatableEntry::make('assignedProcedure.procedureDetails')
                                                        ->label('')
                                                        ->schema([
                                                            TextEntry::make('procedure.name')->label('Название процедуры'),
                                                            TextEntry::make('sessions')->label('Сеансы'),
                                                            TextEntry::make('price')->label('Цена')->formatStateUsing(fn($state) => number_format($state, 0) . ' so‘m'),
                                                        ])
                                                        ->columns(5)
                                                        ->default([]),
                                                        
                                                                                                
                                                Grid::make(3)
                                                    ->schema([
                                                        TextEntry::make('assignedProcedure.total_cost')
                                                            ->label('Umumiy summa')
                                                            ->color(Color::Blue)
                                                            ->badge()
                                                            ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . ' сум'),

                                                        TextEntry::make('assignedProcedure.total_paid_amount')
                                                            ->label('To‘langan')
                                                            ->color(Color::Green)
                                                            ->badge()
                                                            ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . ' сум'),
                                                            
                                                        TextEntry::make('assignedProcedure.total_debt_amount')
                                                            ->label('Qarzdorlik')
                                                            ->color(Color::Red)
                                                            ->badge()
                                                            ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . ' сум'),
                                                    ])
                                    ]),
                            ]),
                            
                        // Yashash joyi tab
                        Tabs\Tab::make('Информация о палате')
                            ->icon('heroicon-o-home')
                            ->schema([
                                Section::make('')
                                    ->schema([
                                        Section::make()
                                            ->schema([
                                                
                                                Grid::make(3)
                                                    ->schema([
                                                        TextEntry::make('accommodation.admission_date')
                                                            ->label('Kelgan sana')
                                                            ->dateTime('d.m.Y H:i')
                                                            ->placeholder('Kiritilmagan'),
                                                            
                                                        TextEntry::make('accommodation.discharge_date')
                                                            ->label('Chiqish sanasi')
                                                            ->date('d.m.Y')
                                                            ->placeholder('Belgilanmagan'),
                                                    ]),
                                                Grid::make(3)
                                                    ->schema([
                                                        TextEntry::make('accommodation.ward.name')
                                                            ->label('Palata')
                                                            ->weight(FontWeight::Bold)
                                                            ->color(Color::Blue)
                                                            ->placeholder('Tayinlanmagan'),
                                                            
                                                        TextEntry::make('accommodation.bed.number')
                                                            ->label('Karavot raqami')
                                                            ->weight(FontWeight::Bold)
                                                            ->color(Color::Green)
                                                            ->placeholder('Tayinlanmagan'),
                                                            
                                                        TextEntry::make('accommodation.tariff.name')
                                                            ->label('Tarif')
                                                            ->badge()
                                                            ->color(Color::Orange)
                                                            ->placeholder('Tayinlanmagan'),
                                                    ]),
                                                    
                                                    
                                                Grid::make(1)
                                                    ->schema([
                                                        TextEntry::make('accommodation.mealType.daily_price')
                                                            ->label('Ovqatlanish turi')
                                                            ->badge()
                                                            ->color(Color::Purple)
                                                            ->placeholder('Tayinlanmagan'),
                                                    ]),
                                                    
                                                Grid::make(3)
                                                    ->schema([
                                                        TextEntry::make('accommodation.statusPayment.name')
                                                            ->label('To\'lov holati')
                                                            ->badge()
                                                            ->color(fn($state) => match($state) {
                                                                'To\'langan' => Color::Green,
                                                                'To\'lanmagan' => Color::Red,
                                                                'Qisman to\'langan' => Color::Orange,
                                                                default => Color::Gray
                                                            }),
                                                        TextEntry::make('created_at')
                                                            ->label('Yaratilgan')
                                                            ->dateTime('d.m.Y H:i'),
                                                            
                                                        TextEntry::make('accommodation.createdBy.name')
                                                            ->label('Yaratgan')
                                                            ->placeholder('Noma\'lum'),
                                                    ])
                                            ])
                                    ]),
                            ]),
                    ])->columnspan(12)
                    ->activeTab(1)
                    ->persistTabInQueryString()
            ]);
    }
    
    public function getTitle(): string
    {
        return 'Истории Болезно: ' . '№'.$this->record->id . ' - '. $this->record->patient->full_name;
    }
}
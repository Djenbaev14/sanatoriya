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
                                                    ->label('ФИО')
                                                    ->weight(FontWeight::Bold),
                                                    
                                                TextEntry::make('patient.birth_date')
                                                    ->label('Дата рождения')
                                                    ->date('d.m.Y')
                                                    ->placeholder('Не добавлено'),
                                                    
                                                TextEntry::make('patient.phone')
                                                    ->label('Телефон')
                                                    ->placeholder('Не добавлено'),
                                                    
                                                TextEntry::make('patient.address')
                                                    ->label('Адрес')
                                                    ->placeholder('Не добавлено'),
                                            ])
                                            ->columnSpan(1),
                                            
                                        Section::make('Медицинские показатели')
                                            ->icon('heroicon-o-heart')
                                            ->schema([
                                                TextEntry::make('height')
                                                    ->label('Рост (см)')
                                                    ->suffix(' sm')
                                                    ->placeholder('Неизмеренный'),
                                                TextEntry::make('weight')
                                                    ->label('Вес (кг)')
                                                    ->suffix(' kg')
                                                    ->placeholder('Неизмеренный'),
                                                    
                                                TextEntry::make('temperature')
                                                    ->label('Температура')
                                                    ->suffix(' °C')
                                                    ->placeholder('Неизмеренный'),
                                                    
                                                TextEntry::make('disability_types')
                                                    ->label('Виды инвалидности')
                                                    ->formatStateUsing(function ($state) {
                                                        if (empty($state)) {
                                                            return 'Нет';
                                                        }
                                                        return is_array($state) ? implode(', ', $state) : $state;
                                                    })
                                                    ->placeholder('Нет'),
                                                    
                                                TextEntry::make('side_effects')
                                                    ->label('Побочные эффекты')
                                                    ->placeholder('Нет')
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
                                                    ->label('Экстренное положение')
                                                    ->formatStateUsing(fn($state) => $state ? 'Да' : 'Нет')
                                                    ->color(fn($state) => $state ? Color::Red : Color::Green)
                                                    ->weight(FontWeight::Bold),
                                                    
                                                TextEntry::make('transport_type')
                                                    ->label('Тип транспорта')
                                                    ->formatStateUsing(function ($state) {
                                                        return match($state) {
                                                            'ambulance' => 'Tez yordam',
                                                            'family' => 'Oila a\'zolari',
                                                            'self' => 'O\'zi',
                                                            'taxi' => 'Taksi',
                                                            'other' => 'Boshqa',
                                                            default => 'Не добавлено'
                                                        };
                                                    })
                                                    ->placeholder('Не добавлено'),
                                                    
                                                TextEntry::make('referred_from')
                                                    ->label('Откуда отправлено')
                                                    ->formatStateUsing(function ($state) {
                                                        return match($state) {
                                                            'clinic' => 'Poliklinika',
                                                            'hospital' => 'Kasalxona',
                                                            'emergency' => 'Tez yordam',
                                                            'self' => 'O\'zi murojaat',
                                                            'other' => 'Boshqa',
                                                            default => 'Не добавлено'
                                                        };
                                                    })
                                                    ->placeholder('Не добавлено'),
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
                                                    ->label('Создатель')
                                                    ->placeholder('Неизвестный'),
                                                    
                                                TextEntry::make('created_at')
                                                    ->label('Дата создания')
                                                    ->dateTime('d.m.Y H:i'),
                                                    
                                                TextEntry::make('updated_at')
                                                    ->label('Дата изменения')
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
                                    ->label('Медицинские осмотры')
                                    ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                    TextEntry::make('medicalInspection.id')
                                                        ->label('Скачать осмотр')
                                                        ->visible(fn ($record) => $record->medicalInspection !== null)
                                                        ->url(fn ($state) => route('download.inspection', $state))
                                                        ->openUrlInNewTab()
                                                        ->icon('heroicon-o-arrow-down-tray')
                                                        ->color(Color::Gray),
                                                        TextEntry::make('medicalInspection.initialDoctor.name')
                                                            ->label('Приемный  врач')
                                                            ->weight(FontWeight::Bold)
                                                            ->color(Color::Blue)
                                                            ->placeholder('Не назначено'),
                                                            
                                                        TextEntry::make('medicalInspection.assignedDoctor.name')
                                                            ->label('Назначенный врач')
                                                            ->weight(FontWeight::Bold)
                                                            ->color(Color::Green)
                                                            ->placeholder('Не назначено'),
                                                    ]),
                                                    
                                                TextEntry::make('medicalInspection.admission_diagnosis')
                                                    ->label('Диагноз')
                                                    ->placeholder('Не добавлено')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('medicalInspection.complaints')
                                                    ->label('Жалобы')
                                                    ->placeholder('Нет')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('medicalInspection.medical_history')
                                                    ->label('ANAMNEZIS MORBI')
                                                    ->placeholder('Не добавлено')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('medicalInspection.history_life')
                                                    ->label('ANAMNEZIS  VITAE')
                                                    ->placeholder('Не добавлено')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('medicalInspection.epidemiological_history')
                                                    ->label('Эпидемиологический анамнез')
                                                    ->placeholder('Не добавлено')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('medicalInspection.local_state')
                                                    ->label('STATUS LOCALIS')
                                                    ->placeholder('Не добавлено')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('medicalInspection.objectively')
                                                    ->label('STATUS PREZENS OBJECTIVUS')
                                                    ->placeholder('Не добавлено')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('medicalInspection.recommended')
                                                    ->label('Рекомендовано')
                                                    ->placeholder('Не добавлено')
                                                    ->columnSpanFull(),
                                                    
                                                    
                                                Grid::make(2)
                                                    ->schema([
                                                        TextEntry::make('medicalInspection.created_at')
                                                            ->label('Дата создания')
                                                            ->dateTime('d.m.Y H:i'),
                                                            
                                                        TextEntry::make('medicalInspection.updated_at')
                                                            ->label('Дата изменения')
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
                                                            ->label('Назначенный врач')
                                                            ->weight(FontWeight::Bold)
                                                            ->color(Color::Blue)
                                                            ->placeholder('Неизвестный'),
                                                            
                                                        TextEntry::make('labTestHistory.statusPayment.name')
                                                            ->label('Статус платежа')
                                                            ->badge()
                                                            ->color(fn($state) => match($state) {
                                                                'завершенный' => Color::Green,
                                                                'в ожидании' => Color::Red,
                                                                'В кассе' => Color::Orange,
                                                                'отменённый' => Color::Red
                                                            }),
                                                        TextEntry::make('labTestHistory.created_at')
                                                            ->label('Дата создания')
                                                            ->dateTime('d.m.Y H:i'),
                                                    ]),
                                                RepeatableEntry::make('labTestHistory.labTestDetails')
                                                        ->label('')
                                                        ->schema([
                                                            TextEntry::make('lab_test.name')->label('Название анализа'),
                                                            TextEntry::make('sessions')->label('Сеансы'),
                                                            TextEntry::make('price')->label('Цена')->formatStateUsing(fn($state) => number_format($state, 0) . ' so‘m'),
                                                            TextEntry::make('result')->label('Результат')->placeholder('Yo‘q'),
                                                        ])
                                                        ->columns(5)
                                                        ->default([]),
                                                                                                
                                                Grid::make(3)
                                                    ->schema([
                                                        TextEntry::make('labTestHistory.total_cost')
                                                            ->label('Общая сумма')
                                                            ->color(Color::Blue)
                                                            ->badge()
                                                            ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . ' сум'),

                                                        TextEntry::make('labTestHistory.total_paid_amount')
                                                            ->label('Оплачено')
                                                            ->color(Color::Green)
                                                            ->badge()
                                                            ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . ' сум'),
                                                            
                                                        TextEntry::make('labTestHistory.total_debt_amount')
                                                            ->label('Долг')
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
                                                            ->label('Дата создания')
                                                            ->dateTime('d.m.Y H:i'),
                                                        TextEntry::make('assignedProcedure.statusPayment.name')
                                                            ->label('Статус платежа')
                                                            ->badge()
                                                            ->color(fn($state) => match($state) {
                                                                'завершенный' => Color::Green,
                                                                'в ожидании' => Color::Red,
                                                                'В кассе' => Color::Orange,
                                                                'отменённый' => Color::Red
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
                                                            ->label('Общая сумма')
                                                            ->color(Color::Blue)
                                                            ->badge()
                                                            ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . ' сум'),

                                                        TextEntry::make('assignedProcedure.total_paid_amount')
                                                            ->label('Оплачено')
                                                            ->color(Color::Green)
                                                            ->badge()
                                                            ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . ' сум'),
                                                            
                                                        TextEntry::make('assignedProcedure.total_debt_amount')
                                                            ->label('Долг')
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
                                                            ->label('Дата поступления')
                                                            ->dateTime('d.m.Y H:i')
                                                            ->placeholder('Не добавлено'),
                                                            
                                                        TextEntry::make('accommodation.discharge_date')
                                                            ->label('Дата выхода')
                                                            ->date('d.m.Y')
                                                            ->placeholder('Не отмечено'),
                                                    ]),
                                                Grid::make(3)
                                                    ->schema([
                                                        TextEntry::make('accommodation.ward.name')
                                                            ->label('Палата')
                                                            ->weight(FontWeight::Bold)
                                                            ->color(Color::Blue)
                                                            ->placeholder('Не назначено'),
                                                            
                                                        TextEntry::make('accommodation.bed.number')
                                                            ->label('Номер кровати')
                                                            ->weight(FontWeight::Bold)
                                                            ->color(Color::Green)
                                                            ->placeholder('Не назначено'),
                                                            
                                                        TextEntry::make('accommodation.tariff.name')
                                                            ->label('Тариф')
                                                            ->badge()
                                                            ->color(Color::Orange)
                                                            ->placeholder('Не назначено'),
                                                    ]),
                                                    
                                                    
                                                Grid::make(1)
                                                    ->schema([
                                                        TextEntry::make('accommodation.mealType.daily_price')
                                                            ->label('Питание (суточная цена)')
                                                            ->badge()
                                                            ->color(Color::Purple)
                                                            ->placeholder('Не назначено'),
                                                    ]),
                                                    
                                                Grid::make(3)
                                                    ->schema([
                                                        TextEntry::make('accommodation.statusPayment.name')
                                                            ->label('Статус платежа')
                                                            ->badge()
                                                            ->color(fn($state) => match($state) {
                                                                'завершенный' => Color::Green,
                                                                'в ожидании' => Color::Red,
                                                                'В кассе' => Color::Orange,
                                                                'отменённый' => Color::Red
                                                            }),
                                                        TextEntry::make('created_at')
                                                            ->label('Дата создания')
                                                            ->dateTime('d.m.Y H:i'),
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
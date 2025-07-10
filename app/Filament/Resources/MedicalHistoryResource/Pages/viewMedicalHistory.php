<?php

namespace App\Filament\Resources\MedicalHistoryResource\Pages;

use App\Filament\Resources\MedicalHistoryResource;
use Filament\Actions;
use Filament\Infolists\Components\Actions\Action;
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
                            
                        // Yashash joyi tab
                        Tabs\Tab::make('Условия размещения')
                            ->icon('heroicon-o-home')
                            ->schema([
                                    Section::make('Условия размещения')
                                        ->visible(fn ($record) => is_null($record->accommodation) && auth()->user()->can('создать условия размещения'))
                                        ->schema([
                                            \Filament\Infolists\Components\Actions::make([
                                                
                                                Action::make('createAccommodation')
                                                ->label('Создать Условия размещения')
                                                ->icon('heroicon-o-plus')
                                                ->button()
                                                ->color('primary')
                                                ->url(fn ($record) => "/admin/accommodations/create?patient_id={$record->patient->id}&medical_history_id={$record->id}" )
                                            ])
                                        ]),
                                        Grid::make(2)
                                            ->schema([
                                                // Asosiy bemor
                                                Section::make('Основной пациент')
                                                    ->visible(fn ($record) => $record->accommodation !== null)
                                                    ->schema([
                                                        Grid::make(2)->schema([
                                                            TextEntry::make('accommodation.patient.full_name')
                                                                ->label('Пациент')
                                                                ->weight(FontWeight::Bold)
                                                                ->color(Color::Blue),

                                                            TextEntry::make('accommodation.statusPayment.name')
                                                                ->label('Статус платежа')
                                                                ->badge()
                                                                ->color(fn ($state) => match($state) {
                                                                    'завершенный' => Color::Green,
                                                                    'в ожидании' => Color::Red,
                                                                    'В кассе' => Color::Orange,
                                                                    'отменённый' => Color::Red,
                                                                    default => Color::Gray
                                                                }),

                                                            TextEntry::make('accommodation.ward.name')->label('Палата'),
                                                            TextEntry::make('accommodation.bed.number')->label('Койка'),

                                                            TextEntry::make('accommodation.admission_date')
                                                                ->label('Дата поступления')
                                                                ->date('d.m.Y'),

                                                            TextEntry::make('accommodation.discharge_date')
                                                                ->label('Дата выхода')
                                                                ->date('d.m.Y'),

                                                            TextEntry::make('accommodation_days')
                                                                ->label('Кол-во дней')
                                                                ->default(fn ($record) => $record->accommodation->calculateDays()),

                                                            TextEntry::make('accommodation.mealType.daily_price')
                                                                ->label('Питание')
                                                                ->badge()
                                                                ->color(Color::Purple),
                                                            ]),
                                                        Grid::make(1)->schema([
                                                            \Filament\Infolists\Components\Actions::make([
                                                                Action::make('editAccommodation')
                                                                ->label('Редактировать')
                                                                ->visible(fn ($record) => auth()->user()->can('создать условия размещения'))
                                                                ->icon('heroicon-o-pencil')
                                                                ->button()
                                                                ->color('warning')
                                                                ->url(fn ($record) => "/admin/accommodations/{$record->accommodation->id}/edit?patient_id={$record->patient->id}&medical_history_id={$record->id}" )
                                                            ])
                                                        ]),
                                                    ])->columnSpan(6),

                                                // Qarovchi
                                                Section::make('Уход за пациентом')
                                                    ->visible(fn ($record) => $record->accommodation?->partner !== null)
                                                    ->schema([
                                                        Grid::make(2)->schema([
                                                            TextEntry::make('accommodation.partner.patient.full_name')
                                                                ->label('Уходящий')
                                                                ->weight(FontWeight::Bold)
                                                                ->color(Color::Blue),

                                                            TextEntry::make('accommodation.partner.ward.name')->label('Палата'),
                                                            TextEntry::make('accommodation.partner.bed.number')->label('Койка'),

                                                            TextEntry::make('accommodation.partner.admission_date')
                                                                ->label('Дата поступления')
                                                                ->date('d.m.Y'),

                                                            TextEntry::make('accommodation.partner.discharge_date')
                                                                ->label('Дата выхода')
                                                                ->date('d.m.Y'),

                                                            TextEntry::make('partner_days')
                                                                ->label('Кол-во дней')
                                                                ->default(fn ($record) => $record->accommodation->calculatePartnerDays()),
                                                            TextEntry::make('accommodation.partner.mealType.daily_price')
                                                                ->label('Питание')
                                                                ->badge()
                                                                ->color(Color::Purple),
                                                        ])
                                                    ])->columnSpan(6),
                                            ])->columnSpan(12)->columns(12)
       

                            ]),
                            
                        // Tibbiy ko'rik tab
                        Tabs\Tab::make('Приемный Осмотр')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->schema([
                                Section::make('Приемный Осмотр')
                                    ->visible(fn ($record) => is_null($record->medicalInspection) && auth()->user()->can('создать приемный осмотр'))
                                    ->schema([
                                        \Filament\Infolists\Components\Actions::make([
                                            Action::make('createMedicalInspection')
                                            ->label('Создать Приемный Осмотр')
                                            ->icon('heroicon-o-plus')
                                            ->button()
                                            ->color('primary')
                                            ->url(fn ($record) => "/admin/medical-inspections/create?patient_id={$record->patient->id}&medical_history_id={$record->id}" )
                                        ])
                                    ]),
                                Section::make('')
                                    ->label('Приемный Осмотр')
                                    ->visible(fn ($record) => $record->medicalInspection !== null) // 👈 Bu muhim
                                    ->schema([
                                                Grid::make(4)
                                                    ->schema([
                                                    TextEntry::make('medicalInspection.id')
                                                        ->label('Скачать осмотр')
                                                        ->visible(fn ($record) => $record->medicalInspection !== null)
                                                        ->url(fn ($state) => route('download.inspection', $state))
                                                        ->openUrlInNewTab()
                                                        ->formatStateUsing(fn($state) => 'Приемный осмотр №' . $state)
                                                        ->icon('heroicon-o-arrow-down-tray')
                                                        ->color(Color::Gray),
                                                        TextEntry::make('medicalInspection.initialDoctor.name')
                                                            ->label('Приемный  врач')
                                                            ->weight(FontWeight::Bold)
                                                            ->color(Color::Blue)
                                                            ->placeholder('Не назначено'),
                                                            // assignedDoctor name kiriting
                                                        TextEntry::make('medicalInspection.assignedDoctor.name')
                                                            ->label('Назначенный врач')
                                                            ->weight(FontWeight::Bold)
                                                            ->color(Color::Blue)
                                                            ->placeholder('Не назначено'),

                                                        \Filament\Infolists\Components\Actions::make([
                                                                Action::make('editMedicalInspection')
                                                                ->label('Редактировать')
                                                                ->visible(fn ($record) => $record->medicalInspection !== null && auth()->user()->can('создать приемный осмотр'))
                                                                ->icon('heroicon-o-pencil')
                                                                ->button()
                                                                ->color('warning')
                                                                ->url(fn ($record) => "/admin/medical-inspections/{$record->medicalInspection->id}/edit?patient_id={$record->patient->id}&medical_history_id={$record->id}" )
                                                        ])
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
                            
                        // Tibbiy ko'rik tab
                        Tabs\Tab::make('Отделение Осмотр')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->schema([
                                Section::make('Отделение Осмотр')
                                // auth user medicalInspection dagi bekitilgan assigned_doctor_id teng bolsa kiritsin
                                    ->visible(fn ($record) => is_null($record->departmentInspection) && auth()->user()->can(abilities: 'создать отделение осмотр') && ($record->medicalInspection?->assigned_doctor_id === auth()->id()))
                                    ->schema([
                                        \Filament\Infolists\Components\Actions::make([
                                            
                                            Action::make('createDepartmentInspection')
                                            ->label('Создать Отделение Осмотр')
                                            ->icon('heroicon-o-plus')
                                            ->button()
                                            ->color('primary')
                                            ->url(fn ($record) => "/admin/department-inspections/create?patient_id={$record->patient->id}&medical_history_id={$record->id}" )
                                        ])
                                    ]),
                                Section::make('')
                                    ->label('Отделение Осмотр')
                                    ->visible(fn ($record) => $record->departmentInspection !== null) // 👈 Bu muhim
                                    ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextEntry::make('departmentInspection.id')
                                                            ->label('Скачать осмотр')
                                                            ->visible(fn ($record) => $record->departmentInspection !== null)
                                                            ->url(fn ($state) => route('download.department.inspection', $state))
                                                            ->openUrlInNewTab()
                                                            ->formatStateUsing(fn($state) => 'Отделение осмотр №' . $state)
                                                            ->icon('heroicon-o-arrow-down-tray')
                                                            ->color(Color::Gray),
                                                            
                                                        \Filament\Infolists\Components\Actions::make([
                                                                Action::make('editDepartmentInspection')
                                                                ->label('Редактировать')
                                                                ->visible(fn ($record) => $record->departmentInspection !== null && auth()->user()->can(abilities: 'создать отделение осмотр') && ($record->medicalInspection?->assigned_doctor_id === auth()->id()))
                                                                ->icon('heroicon-o-pencil')
                                                                ->button()
                                                                ->color('warning')
                                                                ->url(fn ($record) => "/admin/department-inspections/{$record->departmentInspection->id}/edit?patient_id={$record->patient->id}&medical_history_id={$record->id}" )
                                                        ])
                                                    ]),
                                                    
                                                TextEntry::make('departmentInspection.admission_diagnosis')
                                                    ->label('Диагноз')
                                                    ->placeholder('Не добавлено')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('departmentInspection.complaints')
                                                    ->label('Жалобы')
                                                    ->placeholder('Нет')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('departmentInspection.medical_history')
                                                    ->label('ANAMNEZIS MORBI')
                                                    ->placeholder('Не добавлено')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('departmentInspection.history_life')
                                                    ->label('ANAMNEZIS  VITAE')
                                                    ->placeholder('Не добавлено')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('departmentInspection.epidemiological_history')
                                                    ->label('Эпидемиологический анамнез')
                                                    ->placeholder('Не добавлено')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('departmentInspection.local_state')
                                                    ->label('STATUS LOCALIS')
                                                    ->placeholder('Не добавлено')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('departmentInspection.objectively')
                                                    ->label('STATUS PREZENS OBJECTIVUS')
                                                    ->placeholder('Не добавлено')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('departmentInspection.treatment')
                                                    ->label('Лечение')
                                                    ->placeholder('Не добавлено')
                                                    ->columnSpanFull(),
                                                    
                                                TextEntry::make('departmentInspection.recommended')
                                                    ->label('Рекомендовано')
                                                    ->placeholder('Не добавлено')
                                                    ->columnSpanFull(),
                                                    
                                                    
                                                Grid::make(2)
                                                    ->schema([
                                                        TextEntry::make('departmentInspection.created_at')
                                                            ->label('Дата создания')
                                                            ->dateTime('d.m.Y H:i'),
                                                            
                                                        TextEntry::make('departmentInspection.updated_at')
                                                            ->label('Дата изменения')
                                                            ->dateTime('d.m.Y H:i'),
                                                    ])
                                    ]),
                            ]),
                            
                        // Laboratoriya testlari tab
                        Tabs\Tab::make('Анализы')
                            ->icon('heroicon-o-beaker')
                            ->schema([
                                Section::make('Анализы')
                                    ->visible(fn ($record) => is_null($record->labTestHistory) && (auth()->user()->can(abilities: 'создать анализы') || ($record->medicalInspection?->assigned_doctor_id === auth()->id())))
                                    ->schema([
                                        \Filament\Infolists\Components\Actions::make([
                                            
                                            Action::make('createLabTestHistory')
                                            ->label('Создать Анализы')
                                            ->icon('heroicon-o-plus')
                                            ->button()
                                            ->color('primary')
                                            ->url(fn ($record) => "/admin/lab-test-histories/create?patient_id={$record->patient->id}&medical_history_id={$record->id}" )
                                        ])
                                    ]),
                                Section::make('Анализы')
                                    ->visible(fn ($record) => $record->labTestHistory !== null) // 👈 Bu muhim
                                    ->schema([
                                                Grid::make(4)
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
                                                        
                                                        \Filament\Infolists\Components\Actions::make([
                                                                Action::make('editLabTestHistory')
                                                                ->label('Редактировать')
                                                                ->visible(fn ($record) => $record->labTestHistory !== null && (auth()->user()->can(abilities: 'создать анализы') || ($record->medicalInspection?->assigned_doctor_id === auth()->id())))
                                                                ->icon('heroicon-o-pencil')
                                                                ->button()
                                                                ->color('warning')
                                                                ->url(fn ($record) => "/admin/lab-test-histories/{$record->labTestHistory->id}/edit?patient_id={$record->patient->id}&medical_history_id={$record->id}" )
                                                        ])
                                                            
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
                                Section::make('Процедуры')
                                    ->visible(fn ($record) => is_null($record->assignedProcedure) && (auth()->user()->can(abilities: 'создать процедуры') || ($record->medicalInspection?->assigned_doctor_id === auth()->id())))
                                    ->schema([
                                        \Filament\Infolists\Components\Actions::make([
                                            
                                            Action::make('createAssignedProcedure')
                                            ->label('Создать Процедуры')
                                            ->icon('heroicon-o-plus')
                                            ->button()
                                            ->color('primary')
                                            ->url(fn ($record) => "/admin/assigned-procedures/create?patient_id={$record->patient->id}&medical_history_id={$record->id}" )
                                        ])
                                    ]),
                                Section::make()
                                    ->visible(fn ($record) => $record->assignedProcedure !== null)
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
                                                            
                                                        \Filament\Infolists\Components\Actions::make([
                                                                Action::make('editAssignedProcedure')
                                                                ->label('Редактировать')
                                                                ->visible(fn ($record) => $record->assignedProcedure !== null && (auth()->user()->can(abilities: 'создать процедуры') || ($record->medicalInspection?->assigned_doctor_id === auth()->id())))
                                                                ->icon('heroicon-o-pencil')
                                                                ->button()
                                                                ->color('warning')
                                                                ->url(fn ($record) => "/admin/assigned-procedures/{$record->assignedProcedure->id}/edit?patient_id={$record->patient->id}&medical_history_id={$record->id}" )
                                                        ])
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
                            
                    ])->columnspan(12)
                    ->activeTab(1)
                    ->persistTabInQueryString()
            ]);
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Редактировать'),
                
        ];
    }
    public function getTitle(): string
    {
        return 'Истории Болезно: ' . '№'.$this->record->number . ' - '. $this->record->patient->full_name;
    }
}
<?php

namespace App\Filament\Resources\MedicalHistoryResource\Pages;

use App\Filament\Resources\MedicalHistoryResource;
use App\Forms\Components\WebcamCapture;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
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
                                                Grid::make()
                                                    ->schema([
                                                        \Filament\Infolists\Components\Fieldset::make('Фото')
                                                            ->schema([
                                                                ImageEntry::make('patient.photo')
                                                                    ->label('')
                                                                    ->height(200)
                                                                    ->width(200),
                                                                \Filament\Infolists\Components\Actions::make([
                                                                    Action::make('add_or_edit_photo')
                                                                        ->label(fn ($record) => $record->patient->photo ? 'Сделать фото' : 'Сделать фото')
                                                                        ->icon(fn ($record) => $record->patient->photo ? 'heroicon-o-pencil-square' : 'heroicon-o-plus-circle')
                                                                        ->form([
                                                                            WebcamCapture::make('photo')
                                                                                ->view('forms.components.webcam-capture')
                                                                                ->columnSpan(12),
                                                                        ])
                                                                        ->action(function ($data, $record) {
                                                                            // eski rasmni o‘chirish
                                                                            if ($record->patient->photo && \Storage::disk('public')->exists($record->patient->photo)) {
                                                                                \Storage::disk('public')->delete($record->patient->photo);
                                                                            }

                                                                            // yangi rasmni saqlash
                                                                            $image = str_replace('data:image/png;base64,', '', $data['photo']);
                                                                            $image = str_replace(' ', '+', $image);
                                                                            $fileName = 'patients/' . uniqid() . '.png';

                                                                            \Storage::disk('public')->put($fileName, base64_decode($image));

                                                                            // bazani yangilash
                                                                            $record->patient->update([
                                                                                'photo' => $fileName,
                                                                            ]);
                                                                        }),
                                                                ])
                                                                ->label(''),
                                                            ]),
                                                    ]),
                                                TextEntry::make('patient.full_name')
                                                    ->label('ФИО')
                                                    ->weight(FontWeight::Bold),
                                                    
                                                TextEntry::make('patient.birth_date')
                                                    ->label('Дата рождения')
                                                    ->date('d.m.Y')
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
                                                    
                                                TextEntry::make('side_effects')
                                                    ->label('Побочные эффекты')
                                                    ->placeholder('Нет')
                                                    ->columnSpanFull(),
                                                TextEntry::make('id')
                                                    ->label('Диагноз')
                                                    ->formatStateUsing(function ($record) {
                                                        return $record->medicalInspection->admission_diagnosis
                                                            ?? $record->medicalInspection?->mkbClass?->name 
                                                            ?? 'Нет';
                                                    })
                                                    ->columnSpanFull(),
                                            ])
                                            ->columnSpan(1),
                                    ]),
                                    
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

                                                            TextEntry::make('accommodation.ward.name')->label('Палата'),

                                                            TextEntry::make('accommodation.admission_date')
                                                                ->label('Дата поступления')
                                                                ->date('d.m.Y'),

                                                            TextEntry::make('accommodation.discharge_date')
                                                                ->label('Дата выпски')
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
                                                                ->visible(fn ($record) => $record->accommodation !== null && auth()->user()->can('создать условия размещения'))
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
                                                    
                                                TextEntry::make('medicalInspection.id')
                                                    ->label('Диагноз')
                                                    ->label('Диагноз')
                                                    ->formatStateUsing(function ($record) {
                                                        return $record->medicalInspection->admission_diagnosis
                                                            ?? $record->medicalInspection?->mkbClass?->name 
                                                            ?? 'Нет';
                                                    })
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
                                                            TextEntry::make('lab_test.name')->label(''),
                                                            // TextEntry::make('sessions')->label('Сеансы'),
                                                            TextEntry::make('price')
                                                            ->visible(fn () => !auth()->user()->hasRole('Доктор'))
                                                            ->label('')->formatStateUsing(fn($state) => number_format($state, 0) . ' сум'),
                                                            // TextEntry::make('result')->label('Результат')->placeholder('Yo‘q'),
                                                        ])
                                                        ->columns(3)
                                                        ->default([]),
                                                                                                
                                                Grid::make(1)
                                                    ->schema([
                                                        TextEntry::make('labTestHistory.total_cost')
                                                            ->label('Общая сумма')
                                                            ->visible(fn () => !auth()->user()->hasRole('Доктор'))
                                                            ->color(Color::Blue)
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
                                                    TextEntry::make('procedure.name')->label(''),
                                                    TextEntry::make('executor.name')->label(''),
                                                    TextEntry::make('sessions')->label('')
                                                        ->html()
                                                        ->formatStateUsing(function ($state, $record) {
                                                            $totalSessions = (int) $state;

                                                            // endi executor_id to'g'ridan-to'g'ri $record ichida
                                                            if (is_null($record->executor_id)) {
                                                                return $totalSessions . ' <span style="color: red;">(Исполнитель не назначен)</span>';
                                                            }

                                                            // endi sessionlar bo'yicha bajarilganini hisoblash
                                                            $completed = \App\Models\ProcedureSession::query()
                                                                ->whereHas('procedureDetail', fn($q) => 
                                                                    $q->where('id', $record->id)
                                                                    ->where('executor_id', $record->executor_id)
                                                                )
                                                                ->where('is_completed', true)
                                                                ->count();

                                                            // ✅ belgilar
                                                            $stars = '';
                                                            for ($i = 1; $i <= $totalSessions; $i++) {
                                                                if ($i <= $completed) {
                                                                    $stars .= "<span>✅</span>";
                                                                } else {
                                                                    $stars .= "<span>❌</span>";
                                                                }
                                                            }

                                                            return $totalSessions . ' ' . $stars;
                                                        }),
                                                    TextEntry::make('price')->label('')
                                                        ->visible(fn() => !auth()->user()->hasRole('Доктор'))
                                                        ->formatStateUsing(fn($state) => number_format($state, 0) . ' сум'),
                                                ])
                                                ->columns(4)
                                                ->default([]),

                                                        
                                                                                                
                                                Grid::make(1)
                                                    ->schema([
                                                        TextEntry::make('assignedProcedure.total_cost')
                                                            ->label('Общая сумма')
                                                            ->visible(fn () => !auth()->user()->hasRole('Доктор'))
                                                            ->color(Color::Blue)
                                                            ->badge()
                                                            ->formatStateUsing(fn($state) => number_format($state, 0, '.', ' ') . ' сум'),

                                                    ])
                                    ]),
                            ]),
                            Tabs\Tab::make('Платежи')
    ->icon('heroicon-o-banknotes')
    ->schema([
        Section::make('Список оплат')
            ->schema([
                RepeatableEntry::make('payments')
                    ->label('')
                    ->schema([
                        TextEntry::make('amount')->label('Сумма')
                            ->getStateUsing(fn($record) => number_format($record->getTotalPaidAmount(),0,',',' ').' сум'),
                        TextEntry::make('paymentType.name')->label('Тип оплаты'),
                        TextEntry::make('created_at')->label('Дата'),
                        \Filament\Infolists\Components\Actions::make([
                            Action::make('view')
                                ->label('просмотр')
                                ->url(fn ($record) => route('filament.admin.resources.kassa-balances.view', ['record' => $record->id]))
                                ->color('primary')
                                ->openUrlInNewTab(),
                            Action::make('receipt')
                                ->label('чек')
                                ->url(fn ($record) => route('payment-log.view', ['record' => $record->id]))
                                ->color('primary')
                                ->openUrlInNewTab(),
                        ]),
                    ])
                    ->columns(4),
            ]),

        Section::make('Финансовая информация')
            ->schema([
                Grid::make(3)
                    ->schema([
                        TextEntry::make('total_cost')
                            ->label('Общая сумма оплаты')
                            ->default(fn ($record) => number_format($record->getTotalCost(), 0, '.', ' ') . ' сум')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('paid_amount')
                            ->label('Оплачено')
                            ->default(fn ($record) => number_format($record->getTotalPaidAmount(), 0, '.', ' ') . ' сум')
                            ->badge()
                            ->color('green'),

                        TextEntry::make('debt_amount')
                            ->label('Долг сумма')
                            ->default(fn ($record) => number_format(max(0, $record->getTotalCost() - $record->getTotalPaidAmount()), 0, '.', ' ') . ' сум')
                            ->badge()
                            ->color('red'),
                    ])
            ])
    ])

                            
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
        return 'Истории Болезни: ' . '№'.$this->record->number . ' - '. $this->record->patient->full_name;
    }
}
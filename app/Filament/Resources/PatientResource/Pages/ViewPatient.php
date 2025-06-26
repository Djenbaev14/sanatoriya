<?php

namespace App\Filament\Resources\PatientResource\Pages;

use App\Filament\Resources\PatientResource;
use Filament\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Support\Enums\FontWeight;

class ViewPatient extends ViewRecord
{
    protected static string $resource = PatientResource::class;
    
    // Custom infolist override qilish
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make()
                    ->schema([
                        Components\Split::make([
                            Components\Grid::make(2)
                                ->schema([
                                    Components\Group::make([
                                        Components\TextEntry::make('full_name')
                                            ->label('ФИО')
                                            ->size(Components\TextEntry\TextEntrySize::Large)
                                            ->weight(FontWeight::Bold)
                                            ->color('primary'),
                                            
                                        Components\TextEntry::make('birth_date')
                                            ->label('Дата рождения')
                                            ->date('d.m.Y')
                                            ->suffix(function ($record) {
                                                $age = $record->birth_date 
                                                    ? now()->diffInYears($record->birth_date)
                                                    : 0;
                                                return " ({$age} лет)";
                                            }),
                                            
                                    ]),
                                    
                                    Components\Group::make([
                                        Components\TextEntry::make('phone')
                                            ->label('Телефон')
                                            ->copyable()
                                            ->copyMessage('Номер скопирован!')
                                            ->url(fn ($record) => 'tel:' . $record->phone), 
                                        Components\TextEntry::make('address')
                                            ->label('Адрес')
                                            ->state(function ($record) {
                                            return $record->country->name .' , '.$record->region->name .' , '.$record->district->name.' , '.$record->address;
                                        }),

                                    ])
                                    ->grow(false),
                                ]),
                        ]),
                    ])->compact()->columnSpan(12),
                    Tabs::make()
                        ->tabs(array_filter([
                            auth()->user()->can('просмотреть истории болезни') ?
                                Tab::make("История болезно")
                                    ->schema([
                                        Components\Group::make([
                                            Components\TextEntry::make('full_name')
                                                ->label(false)
                                                ->formatStateUsing(function ($record) {
                                                    return view('patient.story-painful', [
                                                        'medicalHistories' => $record->medicalHistories,
                                                        'patient' => $record,
                                                    ])->render();
                                                })
                                                ->html()
                                                ->columnSpanFull() // To'liq ustunni egallash
                                                ->extraAttributes([
                                                    'style' => 'width: 100% !important; display: block !important;'
                                                ])
                                        ])->columnSpan(12)->columns(12)
                                    ])->columnSpan(12)->columns(12)
                                : null,
                            
                            auth()->user()->can('Условия размещения') ?
                                Tab::make("Условия размещения")
                                    ->schema([
                                        Components\Group::make([
                                            Components\TextEntry::make('full_name')
                                                ->label(false)
                                                ->formatStateUsing(function ($record) {
                                                    return view('patient.accommodation', [
                                                        'accommodations' => $record->accommodations,
                                                        'patient' => $record,
                                                    ])->render();
                                                })
                                                ->html()
                                                ->columnSpanFull() // To'liq ustunni egallash
                                                ->extraAttributes([
                                                    'style' => 'width: 100% !important; display: block !important;'
                                                ]),
                                        ])->columnSpan(12)->columns(12),
                                    ])->columnSpan(12)->columns(12)
                                :null,
                                
                            auth()->user()->can('просмотр медицинских осмотров') ?
                                Tab::make("Приемный Осмотр")
                                    ->schema([
                                        Components\Group::make([
                                            Components\TextEntry::make('full_name')
                                                ->label(false)
                                                ->formatStateUsing(function ($record) {
                                                    return view('patient.treatment-plan', [
                                                        'medicalInspections' => $record->medicalInspections,
                                                        'patient' => $record,
                                                    ])->render();
                                                })
                                                ->html()
                                                ->columnSpanFull() // To'liq ustunni egallash
                                                ->extraAttributes([
                                                    'style' => 'width: 100% !important; display: block !important;'
                                                ]),
                                        ])->columnSpan(12)->columns(12),
                                    ])->columnSpan(12)->columns(12)
                                :null,
                                
                                
                            auth()->user()->can('просмотр медицинских осмотров') ?
                                Tab::make("Отделение Осмотр")
                                    ->schema([
                                        Components\Group::make([
                                            Components\TextEntry::make('full_name')
                                                ->label(false)
                                                ->formatStateUsing(function ($record) {
                                                    return view('patient.department_inspection', [
                                                        'departmentInspections' => $record->departmentInspections,
                                                        'patient' => $record,
                                                    ])->render();
                                                })
                                                ->html()
                                                ->columnSpanFull() // To'liq ustunni egallash
                                                ->extraAttributes([
                                                    'style' => 'width: 100% !important; display: block !important;'
                                                ]),
                                        ])->columnSpan(12)->columns(12),
                                    ])->columnSpan(12)->columns(12)
                                :null,
                                
                            auth()->user()->can('просмотреть лабораторные тесты') ?
                                Tab::make('Анализи')
                                    ->schema([
                                        Components\Group::make([
                                            Components\TextEntry::make('full_name')
                                                ->label(false)
                                                ->formatStateUsing(function ($record) {
                                                    return view('patient.lab-test', [
                                                        'labTestHistories' => $record->labTestHistories,
                                                        'patient' => $record,
                                                    ])->render();
                                                })
                                                ->html()
                                                ->columnSpanFull() // To'liq ustunni egallash
                                                ->extraAttributes([
                                                    'style' => 'width: 100% !important; display: block !important;'
                                                ]),
                                        ])->columnSpan(12)->columns(12),
                                    ])->columnSpan(12)->columns(12)
                                :null,
                            auth()->user()->can('просмотр процедур') ?
                                Tab::make('Планы лечения')
                                    ->schema([
                                        Components\Group::make([
                                            Components\TextEntry::make('full_name')
                                                ->label(false)
                                                ->formatStateUsing(function ($record) {
                                                    return view('patient.procedure', [
                                                        'assignedProcedures' => $record->assignedProcedures,
                                                        'patient' => $record,
                                                    ])->render();
                                                })
                                                ->html()
                                                ->columnSpanFull() // To'liq ustunni egallash
                                                ->extraAttributes([
                                                    'style' => 'width: 100% !important; display: block !important;'
                                                ]),
                                        ])->columnSpan(12)->columns(12),
                                    ])->columnSpan(12)->columns(12)
                                :null
                        ]))->columnSpan(12)->columns(12),

            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Редактировать'),
                
        ];
    }

    // Custom CSS qo'shish
    protected function getHeaderWidgets(): array
    {
        return [
            // Custom widget qo'shish mumkin
        ];
    }

    // Custom view template ishlatish
    // protected static string $view = 'filament.resources.patient.pages.view-patient';
}

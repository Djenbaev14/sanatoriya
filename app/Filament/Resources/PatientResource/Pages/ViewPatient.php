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
                                            return $record->region->name .','.$record->district->name.','.$record->address;
                                        }),

                                    ])
                                    ->grow(false),
                                ]),
                        ]),
                    ])->compact()->columnSpan(12),
                    Tabs::make()
                        ->tabs([
                            Tab::make("Осмотр и План лечения")
                                ->schema([
                                    Components\Group::make([
                                        Components\TextEntry::make('full_name')
                                            ->label(false)
                                            ->formatStateUsing(function ($record) {
                                                return view('patient.treatment-plan', [
                                                    'medicalHistories' => $record->medicalHistories,
                                                    'patient' => $record,
                                                ])->render();
                                            })
                                            ->html(),
                                    ]),
                                ]),
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
                                            ->html(),
                                    ]),
                                ]),
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
                                            ->html(),
                                    ]),
                                ]),
                        ])->columnSpan(12),

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

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentInspectionResource\Pages;
use App\Filament\Resources\DepartmentInspectionResource\RelationManagers;
use App\Models\DepartmentInspection;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Actions\Action;

class DepartmentInspectionResource extends Resource
{
    protected static ?string $model = DepartmentInspection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Hidden::make('initial_doctor_id')
                            ->default(fn () => auth()->user()->id)
                            ->dehydrated(true),
                        Hidden::make('assigned_doctor_id')
                            ->default(fn () => auth()->user()->id)
                            ->dehydrated(true),
                        Select::make('patient_id')
                            ->label('Пациент')
                            ->disabled()
                            ->relationship('patient', 'full_name') // yoki kerakli atribut
                            ->default(request()->get('patient_id'))
                            ->required()
                            ->columnSpan(12),
                        Select::make('medical_history_id')
                            ->label('История болезно')
                            ->default(request()->get('medical_history_id'))
                            ->required()
                            ->options(function (Get $get, $state) {
                                $patientId = $get('patient_id');

                                if (!$patientId) return [];

                                $query = \App\Models\MedicalHistory::where('patient_id', $patientId)
                                    ->doesntHave('departmentInspection');

                                // 👇 edit holatida tanlangan qiymat chiqsin
                                if ($state) {
                                    $query->orWhere('id', $state); // yoki ->orWhere('id', $state) agar 'id' saqlanayotgan bo‘lsa
                                }

                                return $query->get()->mapWithKeys(function ($history) {
                                    $formattedId = str_pad('№' . $history->number, 10);
                                    $formattedDate = \Carbon\Carbon::parse($history->created_at)->format('d.m.Y H:i');
                                    return [$history->id => $formattedId . ' - ' . $formattedDate];
                                });
                            })
                            ->required()
                            ->columnSpan(6),
                        Textarea::make('complaints')
                            ->label('Жалобы')
                            ->rows(3)
                            ->columnSpan(12),
                        View::make('forms.previous-medical-histories')->columnSpan(12),
                        Textarea::make('medical_history')
                            ->label('ANAMNEZIS  MORBI')
                            ->rows(3)
                            ->columnSpan(12),
                        View::make('forms.previous-history-life')->columnSpan(12),
                        Textarea::make('history_life')
                            ->label('ANAMNEZIS  VITAE')
                            ->rows(3)
                            ->columnSpan(12),
                        View::make('forms.previous-epidemiological-histories')->columnSpan(12),
                        Textarea::make('epidemiological_history')
                            ->label('Эпидемиологический анамнез')
                            ->rows(3)
                            ->columnSpan(12),
                        View::make('forms.previous-objectivelies')->columnSpan(12),
                        Textarea::make('objectively')
                            ->label('STATUS PREZENS OBJECTIVUS')
                            ->rows(3)
                            ->columnSpan(12),
                        View::make('forms.previous-local-states')->columnSpan(12),
                        Textarea::make('local_state')
                            ->label('STATUS LOCALIS')
                            ->rows(3)
                            ->columnSpan(12),
                        View::make('forms.previous-diagnoses')->columnSpan(12),
                        Textarea::make('admission_diagnosis')
                            ->label('Диагноз')
                            ->rows(3)
                            ->columnSpan(12),
                        View::make('forms.previous-recommendeds')->columnSpan(12),
                        Textarea::make('recommended')
                            ->label('Рекомендовано')
                            ->rows(3)
                            ->columnSpan(12),
                        Textarea::make('treatment')
                            ->label('Назначе́ние')
                            ->rows(3)
                            ->columnSpan(12),
                    ])->columns(12)->columnSpan(12)
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
            'index' => Pages\ListDepartmentInspections::route('/'),
            'create' => Pages\CreateDepartmentInspection::route('/create'),
            'edit' => Pages\EditDepartmentInspection::route('/{record}/edit'),
            'view' => Pages\ViewDepartmentInspection::route('/{record}'),
        ];
    }
}

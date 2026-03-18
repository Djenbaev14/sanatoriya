<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalInspectionResource\Pages;
use App\Filament\Resources\MedicalInspectionResource\RelationManagers;
use App\Models\Inspection;
use App\Models\MedicalInspection;
use App\Models\Patient;
use App\Models\PaymentType;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class MedicalInspectionResource extends Resource
{
    protected static ?string $model = MedicalInspection::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Hidden::make('initial_doctor_id')
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
                            ->options(function (Get $get, $state) {
                                $patientId = $get('patient_id');

                                if (!$patientId) return [];

                                $query = \App\Models\MedicalHistory::where('patient_id', $patientId)
                                    ->doesntHave('medicalInspection');

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
                        Select::make('assigned_doctor_id')
                            ->label('Врач')
                            ->options(function (Get $get) {
                                return \App\Models\User::whereHas('roles', function (Builder $query)  {
                                    $query->where('name', 'Доктор');
                                })->pluck('name', 'id');
                            })
                            ->required()
                            ->columnSpan(6),
                        Textarea::make('complaints')
                            ->label('Жалобы')
                            ->rows(3)
                            ->columnSpan(12),
                        Textarea::make('medical_history')
                            ->label('ANAMNEZIS  MORBI')
                            ->rows(3)
                            ->columnSpan(12),
                        Textarea::make('history_life')
                            ->label('ANAMNEZIS  VITAE')
                            ->rows(3)
                            ->columnSpan(12),
                        Textarea::make('epidemiological_history')
                            ->label('Эпидемиологический анамнез')
                            ->rows(3)
                            ->columnSpan(12),
                        Textarea::make('objectively')
                            ->label('STATUS PREZENS OBJECTIVUS')
                            ->rows(3)
                            ->columnSpan(12),
                        Textarea::make('local_state')
                            ->label('STATUS LOCALIS')
                            ->rows(3)
                            ->columnSpan(12),
                        Textarea::make('recommended')
                            ->label('Рекомендовано')
                            ->rows(3)
                            ->columnSpan(12),
                        Select::make('mkb_class_id')
                            ->label('Диагноз')
                            ->options(
                                \App\Models\MkbClass::all()
                                    ->mapWithKeys(fn ($mkb) => [
                                        $mkb->id => $mkb->name
                                    ])
                            )
                            ->searchable()
                            ->columnSpan(12),
                    ])->columns(12)->columnSpan(12)
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

    
    public static function getNavigationLabel(): string
    {
        return 'Осмотр'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Осмотр'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Осмотр'; // Rus tilidagi ko'plik shakli
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedicalInspections::route('/'),
            'create' => Pages\CreateMedicalInspection::route('/create'),
            'edit' => Pages\EditMedicalInspection::route('/{record}/edit'),
            'view' => Pages\ViewMedicalInspection::route('/{record}'),
        ];
    }
}

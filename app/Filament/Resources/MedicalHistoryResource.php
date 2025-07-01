<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalHistoryResource\Pages;
use App\Filament\Resources\MedicalHistoryResource\RelationManagers;
use App\Models\AssignedProcedure;
use App\Models\Bed;
use App\Models\DailyService;
use App\Models\Inspection;
use App\Models\LabTest;
use App\Models\MealType;
use App\Models\MedicalHistory;
use App\Models\MedicalMeal;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Procedure;
use App\Models\ReturnedProcedure;
use App\Models\Tariff;
use App\Models\Ward;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class MedicalHistoryResource extends Resource
{
    protected static ?string $model = MedicalHistory::class;
    protected static ?string $navigationIcon = 'fas-file-medical';
    public static function form(Form $form): Form{
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('patient_id')
                            ->label('Пациент')
                            ->relationship('patient', 'full_name') // Eager load orqali
                            ->required()
                            ->columnSpan(12)
                            ->searchable(   )
                            ->default(fn () => request()->get('patient_id'))
                            ->options(function () {
                                // Agar URL orqali kelgan bo‘lmasa, barcha bemorlar ro'yxatini chiqaramiz
                                return \App\Models\Patient::pluck('full_name', 'id');
                            })
                            ->disabled(fn () => filled(request()->get('patient_id'))), // Faqat URLda bo‘lsa bloklanadi
                        Hidden::make('created_id')
                            ->default(fn () => auth()->user()->id)
                            ->dehydrated(true),
                        TextInput::make('number')
                            ->label('Номер')
                            ->default(fn () =>MedicalHistory::max('number') + 1)
                            ->required()
                            ->columnSpan(4),
                        TextInput::make('height')
                            ->label('рост')
                            ->required()
                            ->suffix('sm')
                            ->columnSpan(4),
                        TextInput::make('weight')
                            ->label('вес')
                            ->suffix('kg')
                            ->required()
                            ->columnSpan(4),
                        TextInput::make('temperature')
                            ->label('температура')
                            ->suffix('°C')
                            ->required()
                            ->columnSpan(4),
                        Select::make('disability_types')
                            ->label('Nogironlik turi')
                            ->multiple()
                            ->options([
                                'no' => "Yo'q",
                                'physical' => 'Jismoniy',
                                'visual' => 'Ko‘rish',
                                'hearing' => 'Eshitish',
                                'mental' => 'Aqliy',
                                'speech' => 'Nutq',
                                'other' => 'Boshqa',
                            ])
                            ->required()
                            ->searchable()
                            ->columnSpan(4),
                        Select::make('referred_from')
                            ->label('Qayerdan yuborilgan?')
                            ->options([
                                'clinic' => 'Poliklinika',
                                'hospital' => 'Shifoxona',
                                'emergency' => 'Tez yordam',
                                'self' => 'O‘zi kelgan',
                                'other' => 'Boshqa',
                            ])
                            ->searchable()
                            ->required()
                            ->columnSpan(4),
                        Select::make('transport_type')
                            ->label('Qanday transportda keldi?')
                            ->options([
                                'ambulance' => 'Tez yordam',
                                'family' => 'Yaqinlari olib kelgan',
                                'self' => 'O‘zi kelgan',
                                'taxi' => 'Taksi',
                                'other' => 'Boshqa',
                            ])
                            ->searchable()
                            ->required()
                            ->columnSpan(4),
                        Textarea::make('side_effects')
                            ->label("Dorilarning nojo'ya ta'siri")
                            ->rows(4)
                            ->placeholder("Masalan: Allergik toshmalar, bosh aylanishi...")
                            ->columnSpan(4),
                        Radio::make('is_emergency')
                            ->required()
                            ->label('Shoshilinch holatda keltirildimi?')
                            ->options([
                                '1' => 'ha',
                                '0'=> "yo'q",
                            ])
                            ->columnSpan(4),
            ])->columns(12)->columnSpan(12),
                    
        ]);
    }
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('Номер')->searchable()->sortable(),
                TextColumn::make('patient.full_name')->label('ФИО')->searchable()->sortable(),
                IconColumn::make('accommodation')
                    ->label('Условия размещения')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->accommodation))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('medicalInspection')
                    ->label('Приемный Осмотр')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->medicalInspection))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('departmentInspection')
                    ->label('Отделение Осмотр')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->departmentInspection))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('labTestHistory')
                    ->label('Анализы')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->labTestHistory))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('assignedProcedure')
                    ->label('Назначенные процедуры')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->assignedProcedure))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')->label('Дата')->dateTime()->sortable(),
                // TextColumn::make('accommodation.discharge_date')->label('Дата выписки')->dateTime()->sortable(),,
            ])
            ->defaultPaginationPageOption(50)
            ->defaultSort('number', 'desc')
            ->filters([
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getNavigationLabel(): string
    {
        return 'Истории болезно'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Истории болезно'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Истории болезно'; // Rus tilidagi ko'plik shakli
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
            'index' => Pages\ListMedicalHistories::route('/'),
            'create' => Pages\CreateMedicalHistory::route('/create'),
            'edit' => Pages\EditMedicalHistory::route('/{record}/edit'),
            'view' => Pages\viewMedicalHistory::route('/{record}'),
        ];
    }
}

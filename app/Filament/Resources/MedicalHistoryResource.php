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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class MedicalHistoryResource extends Resource
{
    protected static ?string $model = MedicalHistory::class;
    protected static ?string $navigationGroup = 'Касса';
    protected static ?int $navigationSort = 3;
    public static function form(Form $form): Form{
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('patient_id')
                            ->label('Пациент')
                            ->disabled()
                            ->relationship('patient', 'full_name') // yoki kerakli atribut
                            ->default(request()->get('patient_id'))
                            ->required()
                            ->columnSpan(12),
                        Hidden::make('created_id')
                            ->default(fn () => auth()->user()->id)
                            ->dehydrated(true),
                            
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
        return false;
    }

    
    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             TextColumn::make('patient.full_name')->label('ФИО')->searchable()->sortable(),
    //             TextColumn::make('total_paid')
    //                 ->label('Обшый сумма')
    //                 ->getStateUsing(function ($record) {
    //                     return number_format($record->getTotalCost(),0,'.',' ').' сум';
    //                 }),
    //             TextColumn::make('created_at')->searchable()->sortable(),
    //         ])
    //         ->filters([
    //             //
    //         ])
    //         ->actions([
    //             Action::make('add_payment')
    //                     ->label('Оплата')
    //                     ->icon('heroicon-o-credit-card')
    //                     ->color('success')
    //                     ->modalWidth(MaxWidth::TwoExtraLarge)
    //                     ->form([
    //                         Section::make('Данные платежа')
    //                             ->schema([
    //                                 Grid::make(2)
    //                                     ->schema([
    //                                         TextInput::make('total_cost')
    //                                             ->label('Общие')
    //                                             ->disabled()
    //                                             ->default(function ($record) {
    //                                                 return number_format($record->getTotalCost(), 0, '.', ' ') . ' сум';
    //                                             }),
                                                
    //                                         TextInput::make('total_paid')
    //                                             ->label('Оплачено')
    //                                             ->disabled()
    //                                             ->default(function ($record) {
    //                                                 return number_format($record->getTotalPaidAmount(), 0, '.', ' ') . ' сум';
    //                                             }),
    //                                     ]),
                                        
    //                                 TextInput::make('remaining')
    //                                     ->label('Остаток')
    //                                     ->disabled()
    //                                     ->default(function ($record) {
    //                                         $remaining = $record->getTotalCost() - $record->getTotalPaidAmount();
    //                                         return number_format($remaining, 0, '.', ' ') . ' сум';
    //                                     }),
    //                             ]),
                                
    //                         Section::make('')
    //                             ->schema([
    //                                 TextInput::make('amount')
    //                                     ->label('Сумма')
    //                                     ->numeric()
    //                                     ->required()
    //                                     ->minValue(0.01)
    //                                     ->step(0.01)
    //                                     ->suffix('сум')
    //                                     ->placeholder('0.00')
    //                                     ->live()
    //                                     ->afterStateUpdated(function ($state, $set, $record) {
    //                                         $remaining = $record->getTotalCost() - $record->getTotalPaidAmount();
    //                                         if ($state > $remaining) {
    //                                             $set('amount', $remaining);
    //                                         }
    //                                     }),
    //                                 Select::make('payment_type_id')
    //                                     ->label('Тип оплаты')
    //                                     ->options(PaymentType::all()->pluck('name', 'id'))
    //                                     ->required(),
                                        
    //                                 Textarea::make('description')
    //                                     ->label('Izoh')
    //                                     ->placeholder('Коммент')
    //                                     ->maxLength(255)
    //                                     ->rows(3),
    //                             ]),
    //                     ])
    //                     ->action(function (array $data, $record) {
    //                         // To'lovni saqlash
    //                         \App\Models\Payment::create([
    //                             'patient_id' => $record->patient_id,
    //                             'lab_test_history_id' => $record->id,
    //                             'amount' => $data['amount'],
    //                             'payment_type_id' => $data['payment_type_id'],
    //                             'description' => $data['description'] ?? null,
    //                         ]);

    //                         // Muvaffaqiyat xabari
    //                         Notification::make()
    //                             ->title('Выплата успешно добавлена!')
    //                             ->success()
    //                             ->body("Оплата: " . number_format($data['amount'], 2) . " сум")
    //                             ->send();
    //                     })
    //                     ->modalHeading('Оплата')
    //                     ->modalSubmitActionLabel('Сохранить')
    //                     ->modalCancelActionLabel('Отмена'),
    //         ])
    //         ->bulkActions([
    //             Tables\Actions\BulkActionGroup::make([
    //                 Tables\Actions\DeleteBulkAction::make(),
    //             ]),
    //         ]);
    // }

    public static function getNavigationLabel(): string
    {
        return 'Истории'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Истории'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Истории'; // Rus tilidagi ko'plik shakli
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

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignedProcedureResource\Pages;
use App\Filament\Resources\AssignedProcedureResource\RelationManagers;
use App\Models\AssignedProcedure;
use App\Models\PaymentType;
use Carbon\Carbon;
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

class AssignedProcedureResource extends Resource
{
    protected static ?string $model = AssignedProcedure::class;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Hidden::make('doctor_id')
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
                            ->required()
                            ->default(request()->get('medical_history_id'))
                            ->label('История болезно')
                            ->reactive()
                            ->options(function (Get $get, $state) {
                                $patientId = $get('patient_id');

                                if (!$patientId) return [];

                                $query = \App\Models\MedicalHistory::where('patient_id', $patientId)
                                    ->doesntHave('assignedProcedure');

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
                            ->columnSpan(4),
                        Repeater::make('procedureDetails')
                                                ->label('')
                                                ->defaultItems(1)
                                                ->relationship('procedureDetails')
                                                ->schema([
                                                    Select::make('procedure_id')
                                                        ->label('Тип процедура')
                                                        ->options(function (Get $get, $state, $context) {
                                                            $selectedIds = collect($get('../../procedureDetails'))
                                                                ->pluck('procedure_id')
                                                                ->filter()
                                                                ->toArray();

                                                            if ($state) {
                                                                $selectedIds = array_diff($selectedIds, [$state]);
                                                            }

                                                            return \App\Models\Procedure::query()
                                                                ->whereNotIn('id', $selectedIds)
                                                                ->pluck('name', 'id');
                                                        })
                                                        ->searchable()
                                                        ->required()
                                                        ->reactive()
                                                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                            $patientId = $get('../../patient_id');
                                                            if (!$patientId || !$state) {
                                                                $set('price', 0);
                                                                return;
                                                            }

                                                            $isForeign = \App\Models\Patient::find($patientId)?->is_foreign;
                                                            $procedure = \App\Models\Procedure::find($state);

                                                            $price = $isForeign == 1 ? $procedure->price_foreign : $procedure->price_per_day;
                                                            $set('price', $price ?? 0);
                                                            $set('total_price', $price * ($get('sessions') ?? 1));

                                                            static::recalculateTotalSum($get, $set);
                                                        })
                                                        ->columnSpan(8),
                                                    TextInput::make('price')
                                                        ->label('Цена')
                                                        ->reactive()
                                                        ->visible(fn () => !auth()->user()->hasRole('Доктор'))
                                                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                                            // sessions maydonini olamiz
                                                            $sessions = $get('sessions') ?? 1;

                                                            // total_price ni hisoblab yangilaymiz
                                                            $set('total_price', $state * $sessions);

                                                            // umumiy summa qayta hisoblanadi
                                                            static::recalculateTotalSum($get, $set);
                                                        })
                                                        ->columnSpan(6),

                                                    TextInput::make('sessions')
                                                        ->label('Кол сеансов')
                                                        ->numeric()
                                                        ->default(1)
                                                        ->required()
                                                        ->reactive()
                                                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                                            $set('total_price', ($get('price') ?? 0) * ($state ?? 1));
                                                            
                                                            static::recalculateTotalSum($get, $set);
                                                        })
                                                        ->columnSpan(4),

                                                    TextInput::make('total_price')
                                                        ->label('Общая стоимость')
                                                        ->disabled()
                                                        ->visible(fn () => !auth()->user()->hasRole('Доктор'))
                                                        ->numeric()
                                                        ->columnSpan(6)
                                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                                            static::recalculateTotalSum($get, $set);
                                                        }),
                                                ])
                                                ->afterStateHydrated(function (Get $get, Set $set, $state) {
                                                    $recordId = $get('id');

                                                    foreach ($state as $index => $item) {
                                                        $session = \App\Models\ProcedureSession::where('assigned_procedure_id', $recordId)
                                                            ->where('procedure_id', $item['procedure_id'])
                                                            ->first();

                                                        // if ($session) {
                                                        //     $set("procedureDetails.{$index}.executor_id", $session->executor_id);
                                                        //     $set("procedureDetails.{$index}.time_id", $session->time_id);
                                                        // }
                                                        
                                                        $price = $item['price'] ?? 0;
                                                        $sessions = $item['sessions'] ?? 1;
                                                        $total = $price * $sessions;
                                                        $set("procedureDetails.{$index}.total_price", $total);
                                                    }
                                                })

                                                ->columns(24)->columnSpan(24),
                                                Placeholder::make('total_sum')
                                                    ->label('Общая стоимость (всего)')
                                                    ->visible(fn () => !auth()->user()->hasRole('Доктор'))
                                                    ->content(function (Get $get) {
                                                        $items = $get('procedureDetails') ?? [];
                                                        $total = collect($items)->sum('total_price');
                                                        return number_format($total, 2, '.', ' ') . ' сум';
                                                    })
                                                    ->columnSpanFull(), 
                    ])->columnSpan(24)->columns(24),
                                                ]);
    }
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    
    protected static function recalculateTotalSum(Get $get, Set $set): void
    {
        $items = $get('assignedProcedures') ?? [];
        $total = collect($items)->sum('total_price');
        $set('total_sum', $total);
    }

    

    public static function getNavigationLabel(): string
    {
        return 'Процедуры'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Процедуры'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Процедуры'; // Rus tilidagi ko'plik shakli
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
            'index' => Pages\ListAssignedProcedures::route('/'),
            'create' => Pages\CreateAssignedProcedure::route('/create'),
            'edit' => Pages\EditAssignedProcedure::route('/{record}/edit'),
            'view' => Pages\ViewAssignedProcedure::route('/{record}'),
        ];
    }
}

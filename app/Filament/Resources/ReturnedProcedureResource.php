<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnedProcedureResource\Pages;
use App\Filament\Resources\ReturnedProcedureResource\RelationManagers;
use App\Models\AssignedProcedure;
use App\Models\MedicalHistory;
use App\Models\Patient;
use App\Models\Procedure;
use App\Models\ProcedureDetail;
use App\Models\ReturnedProcedure;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReturnedProcedureResource extends Resource
{
    protected static ?string $model = ReturnedProcedure::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    
    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Hidden::make('patient_id')
                ->default(fn () => AssignedProcedure::find(request()->get('assigned-procedure'))->patient_id)
                ->dehydrated(true),
            Hidden::make('medical_history_id')
                ->default(fn () => AssignedProcedure::find(request()->get('assigned-procedure'))->medical_history_id)
                ->dehydrated(true),
            Hidden::make('assigned_procedure_id')
                ->default(fn () => request()->get('assigned-procedure'))
                ->dehydrated(true),
            // Select::make('assigned_procedure_id')
            //     ->label('Назначенная процедура')
            //     ->default(request()->get('assigned-procedure'))
            //     ->options(
            //         \App\Models\AssignedProcedure::all()->pluck('created_at', 'number')->mapWithKeys(function ($createdAt, $id,$number) {
            //             $formattedId = str_pad('№'.$number, 10); // 10 ta belgigacha bo‘sh joy qo‘shiladi
            //                 return [$id => $formattedId . \Carbon\Carbon::parse($createdAt)->format('d.m.Y H:i')];
            //             })
            //     )
            //     ->required()
            //     ->reactive(),

            // Qaytariladigan protseduralar
            Repeater::make('returnedProcedureDetails')
                ->relationship('returnedProcedureDetails')
                ->label('Возврат процедур')
                ->schema([
                    Select::make('procedure_id')
                        ->label('Тип процедура')
                        ->options(function (Get $get) {
                            $assignedProcedureId = $get('../../assigned_procedure_id');
                            if (!$assignedProcedureId) return [];

                            return ProcedureDetail::where('assigned_procedure_id', $assignedProcedureId)
                                ->with('procedure')
                                ->get()
                                ->mapWithKeys(fn ($detail) => [$detail->procedure_id => $detail->procedure->name]);
                        })
                        ->reactive()
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                            $patientId = $get('../../patient_id'); // yoki `request()->get('patient_id')`
                                if (!$patientId || !$state) {
                                    $set('price', 0);
                                    return;
                                }

                                $isForeign = Patient::find($patientId)?->is_foreign;

                                $procedure = Procedure::find($state);
                                $price = $isForeign == 1 ? $procedure->price_foreign : $procedure->price_per_day;

                                $set('price', $price ?? 0);
                                $set('total_price', $price * ($get('sessions') ?? 1));
                                
                                static::recalculateTotalSum($get, $set);
                        })
                        ->columnSpan(4),

                    TextInput::make('price')
                        ->label('Цена')
                        ->numeric()
                        ->readOnly()
                        ->columnSpan(2),

                    TextInput::make('sessions')
                        ->label('Возврат сеансов')
                        ->numeric()
                        ->default(1)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                            $set('total_price', ($get('price') ?? 0) * ($state ?? 1));
                        })
                        ->maxValue(function (Get $get) {
                            return $get('sessions') ?? 1;
                        })
                        ->columnSpan(3),

                    TextInput::make('total_price')
                        ->label('Общая стоимость')
                        ->numeric()
                        ->disabled()
                        ->columnSpan(3),
                ])
                ->columns(12)
                ->columnSpan(12)
                ->afterStateHydrated(function (Get $get, Set $set, $state) {
                    // assigned_procedure_id bo‘yicha avtomatik yuklash
                    $assignedProcedureId = $get('assigned_procedure_id');
                    if (!$assignedProcedureId) return;

                    $details = ProcedureDetail::where('assigned_procedure_id', $assignedProcedureId)->get();

                    $defaultState = $details->map(function ($detail) {
                        $total = $detail->price * $detail->sessions;
                        return [
                            'procedure_id' => $detail->procedure_id,
                            'price' => $detail->price,
                            'sessions' => $detail->sessions, // qaytariladigan default sessions (moslashtirsa bo‘ladi)
                            'total_price' => $total,
                        ];
                    })->toArray();

                    $set('returnedProcedureDetails', $defaultState);
                }),
                        Placeholder::make('total_sum')
                                                    ->label('Общая стоимость (всего)')
                                                    ->content(function (Get $get) {
                                                        $items = $get('returnedProcedureDetails') ?? [];
                                                        $total = collect($items)->sum('total_price');
                                                        return number_format($total, 2, '.', ' ') . ' сум';
                                                    })
                                                    ->columnSpanFull(), 
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
            'index' => Pages\ListReturnedProcedures::route('/'),
            'create' => Pages\CreateReturnedProcedure::route('/create'),
            'edit' => Pages\EditReturnedProcedure::route('/{record}/edit'),
        ];
    }
}

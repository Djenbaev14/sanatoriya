<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectionResource\Pages;
use App\Filament\Resources\SectionResource\RelationManagers;
use App\Models\Section;
use App\Models\User;
use App\Models\Ward;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SectionResource extends Resource
{
    protected static ?string $model = Ward::class;

    public static function getNavigationGroup(): string
    {
        return 'Отчет';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['currentAccommodations.medicalHistory.medicalInspection']);
    }
    public static function table(Table $table): Table
    {
        return $table
        ->header(function () {
            // Statistika olish
            $wardsCount = \App\Models\Ward::count();
            $totalPlaces = \App\Models\Ward::all()->sum(function ($ward) {
                return $ward->total_places;
            });
            $busyBeds = \App\Models\Ward::all()->sum(function ($ward) {
                return $ward->beds_count;
            });
            $freeBeds = \App\Models\Ward::all()->sum(function ($ward) {
                return $ward->available_beds_count;
            });

            return view('filament.components.ward-summary', [
                'wardsCount' => $wardsCount,
                'totalPlaces' => $totalPlaces,
                'busyBeds' => $busyBeds,
                'freeBeds' => $freeBeds,
            ]);
        })
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->sortable(),
                TextColumn::make('total_places')
                    ->label('Всего месты')
                    ->sortable(),
                TextColumn::make('beds_count')
                    ->label('Занято')
                    ->sortable(),
                TextColumn::make('available_beds_count')
                    ->label('Свободно')
                    ->sortable(),
                TextColumn::make('current_patients_display')
                    ->label('Больные')
                    ->wrap()
                    ->html(),
            ])
            ->defaultPaginationPageOption(50);
            // ->filters([
                
            // SelectFilter::make('doctor')
            //     ->label('Врач')
            //     ->options(function () {
            //         return \App\Models\User::role('Доктор')
            //             ->pluck('name', 'id')
            //             ->toArray();
            //     })
            //     ->query(function (Builder $query, array $data): Builder {
            //         if (filled($data['value'])) {
            //             return $query->whereHas('currentAccommodations.medicalHistory.medicalInspection', function ($q) use ($data) {
            //                 $q->where('assigned_doctor_id', $data['value']);
            //             });
            //         }
            //         return $query;
            //     })
            // ],layout:FiltersLayout::AboveContent);
            
    }
    public static function canAccess(): bool
    {
        return auth()->user()?->can('Отчет');
    }

    
    public static function getNavigationLabel(): string
    {
        return 'Общее Отделение'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Общее Отделение'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Общее Отделение'; // Rus tilidagi ko'plik shakli
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
            'index' => Pages\ListSections::route('/'),
            'create' => Pages\CreateSection::route('/create'),
            'edit' => Pages\EditSection::route('/{record}/edit'),
        ];
    }
}

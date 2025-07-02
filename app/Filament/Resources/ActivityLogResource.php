<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Filament\Resources\ActivityLogResource\RelationManagers;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    public static function getModel(): string
    {
        return Activity::class;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
    public static function shouldRegisterNavigation(): bool
    {
        return false; // Foydalanuvchi faqat ruxsat berilgan bo'lsa ko'rsatadi
    }
    
    public static function getNavigationLabel(): string
    {
        return 'Лог'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Лог'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Лог'; // Rus tilidagi ko'plik shakli
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('log_name')->label('Log nomi'),
                TextColumn::make('description')->label('Harakat turi'),
                TextColumn::make('subject_type')->label('Model turi'),
                TextColumn::make('subject_id')->label('Model ID'),
                TextColumn::make('causer.name')->label('Foydalanuvchi'),
                TextColumn::make('properties')
                    ->label('O‘zgargan maydonlar')
                    ->formatStateUsing(function ($state) {
                        if (!is_array($state)) {
                            $state = json_decode($state, true);
                        }

                        return '<pre>' . htmlentities(json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) . '</pre>';
                    })
                    ->html()
                    ->wrap(),
                TextColumn::make('created_at')->label('Vaqt')->dateTime('d.m.Y H:i'),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(50);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}

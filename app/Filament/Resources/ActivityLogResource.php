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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'create' => Pages\CreateActivityLog::route('/create'),
            'edit' => Pages\EditActivityLog::route('/{record}/edit'),
        ];
    }
}

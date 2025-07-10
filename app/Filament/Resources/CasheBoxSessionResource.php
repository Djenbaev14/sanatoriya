<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CasheBoxSessionResource\Pages;
use App\Filament\Resources\CasheBoxSessionResource\RelationManagers;
use App\Models\CasheBoxSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CasheBoxSessionResource extends Resource
{
    protected static ?string $model = CasheBoxSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }


    
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('date')->label('Дата')->date('d.m.Y'),
            TextColumn::make('openedBy.name')->label('Открыл'),
            TextColumn::make('closedBy.name')->label('Закрыл'),
            TextColumn::make('opening_amount')->label('Начальная сумма')->money('UZS', true),
            TextColumn::make('closing_amount')->label('Конечная сумма')->money('UZS', true),
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
            'index' => Pages\ListCasheBoxSessions::route('/'),
            'create' => Pages\CreateCasheBoxSession::route('/create'),
            'edit' => Pages\EditCasheBoxSession::route('/{record}/edit'),
        ];
    }
}

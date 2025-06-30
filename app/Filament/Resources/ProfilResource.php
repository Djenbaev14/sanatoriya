<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfilResource\Pages;
use App\Filament\Resources\ProfilResource\RelationManagers;
use App\Models\Profil;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class ProfilResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Профиль';
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = null;

    public static function getNavigationSort(): ?int
    {
        return 999; // eng oxiriga tushadi
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                        Section::make('Профиль')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Имя')
                                    ->required()
                                    ->columnSpan(12),
                                Forms\Components\TextInput::make('username')
                                    ->label('Логин')
                                    ->required()
                                    ->columnSpan(12),
                                Forms\Components\TextInput::make('password')
                                    ->label('Новый пароль')
                                    ->password()
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->maxLength(255)
                                    ->columnSpan(12),
                            ])->columns(12)->columnSpan(12),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    // public static function getPages(): array
    // {
    //     return [
    //         'index' => Pages\ListProfils::route('/'),
    //         'create' => Pages\CreateProfil::route('/create'),
    //         'edit' => Pages\EditProfil::route('/{record}/edit'),
    //     ];
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfils::route('/'),
            'edit' => Pages\EditProfil::route('/{record}/edit'),
        ];
    }

    public static function getNavigationUrl(): string
    {
        return static::getUrl('edit', ['record' => auth()->id()]);
    }

}

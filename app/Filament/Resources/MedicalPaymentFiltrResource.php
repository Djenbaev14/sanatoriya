<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalPaymentFiltrResource\Pages;
use App\Filament\Resources\MedicalPaymentFiltrResource\RelationManagers;
use App\Models\MedicalHistory;
use App\Models\MedicalPaymentFiltr;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MedicalPaymentFiltrResource extends Resource
{
    protected static ?string $model = MedicalHistory::class;

    public static function getNavigationGroup(): string
    {
        return 'Отчет';
    }
    
    public static function getNavigationLabel(): string
    {
        return 'От'; // Rus tilidagi nom
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return false; // Foydalanuvchi faqat ruxsat berilgan bo'lsa ko'rsatadi
    }
    
    public static function getModelLabel(): string
    {
        return 'От'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'От'; // Rus tilidagi ko'plik shakli
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\MedicalPayment::route('/'),
        ];
    }
}

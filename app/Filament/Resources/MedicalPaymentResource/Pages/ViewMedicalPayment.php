<?php

namespace App\Filament\Resources\MedicalPaymentResource\Pages;

use App\Filament\Resources\MedicalPaymentResource;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use App\Models\MedicalHistory;
use Filament\Infolists;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;

class ViewMedicalPayment extends ViewRecord
{
    protected static string $resource = MedicalPaymentResource::class;
    protected function getHeaderActions(): array
    {
        return [
            // ActionGroup::make([
                
            // Action::make('pay')
            //     ->label('Оплатить')
            //     ->color('primary')
            //     ->icon('heroicon-o-credit-card')
            //     ->url(fn () => route('filament.admin.resources.payments.create', [
            //         'patient_id' => $this->record->patient_id,
            //         'medical_history_id' => $this->record->id,
            //     ]))
            // ])
        ];
    }


    public function infolist(Infolist $infolist): Infolist
    {

        return $infolist
            ->schema([
                ViewEntry::make('total_cost_summary')->view('custom.medical-history.total-summary')->columnSpanFull(),

                ViewEntry::make('assigned_procedure')->view('custom.medical-history.assigned-procedures')->columnSpanFull(),
                ViewEntry::make('lab_test_history')->view('custom.medical-history.lab-test-histories')->columnSpanFull(),
                ViewEntry::make('accommodation')->view('custom.medical-history.accommodation')->columnSpanFull(),
                ViewEntry::make('accommodation')->view('custom.medical-history.accommodation-partner')->columnSpanFull(),
                ViewEntry::make('payments')->view('custom.medical-history.payments')->columnSpanFull(),
            ]);
    }
    
    
    public function getTitle(): string
    {
        return 'Журнал оплат: ' . '№'.$this->record->number . ' - '. $this->record->patient->full_name;
    }

}

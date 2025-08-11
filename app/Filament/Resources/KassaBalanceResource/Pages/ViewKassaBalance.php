<?php

namespace App\Filament\Resources\KassaBalanceResource\Pages;

use App\Filament\Resources\KassaBalanceResource;
use App\Models\Payment;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\PaymentResource;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;

class ViewKassaBalance extends ViewRecord
{
    protected static string $resource = KassaBalanceResource::class;
    protected static string $view = 'filament.pages.view-payment-resource';

    public $labDetails;
    public $procedureDetails;
    public $accommodationDetails;
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->successNotificationTitle('Платеж удален')
                ->color('danger')
                ->requiresConfirmation()
        ];
    }
    public function mount($record): void
    {
        $this->record = Payment::with([
            'labTestPayments', 
            'procedurePayments', 
            'accommodationPayments'
        ])->findOrFail($record);

        // Lab testlar
        $this->labDetails = $this->record->labTestPayments
            ->flatMap->labTestPaymentDetails
            ->map(function ($detail) {
                return [
                    'name' => $detail->labTest->name ?? '-',
                    'price' => $detail->price,
                    'sessions' => $detail->sessions,
                    'total' => $detail->price * $detail->sessions,
                ];
            })->values()->all();

        // Procedures
        $this->procedureDetails = $this->record->procedurePayments
            ->flatMap->procedurePaymentDetails
            ->map(function ($detail) {
                return [
                    'name' => $detail->procedure->name ?? '-',
                    'price' => $detail->price,
                    'sessions' => $detail->sessions,
                    'total' => $detail->price * $detail->sessions,
                ];
            })->values()->all();

        // Accommodations — ajratish

        $this->accommodationDetails = [
            'main' => [],
            'partner' => [],
        ];

        foreach ($this->record->accommodationPayments as $acc) {
            $data = [
                'tariff_price' => $acc->tariff_price,
                'ward_day' => $acc->ward_day,
                'meal_price' => $acc->meal_price,
                'meal_day' => $acc->meal_day,
                'total' => ($acc->tariff_price * $acc->ward_day) + ($acc->meal_price * $acc->meal_day),
            ];

            if (!empty($acc->medical_history_id)) {
                $this->accommodationDetails['main'][] = $data;
            } else {
                $this->accommodationDetails['partner'][] = $data;
            }
        }

    }
    protected function getViewData(): array
    {
        return [
            'record' => $this->record,
            'labDetails' => $this->labDetails,
            'procedureDetails' => $this->procedureDetails,
            'accommodationDetails' => $this->accommodationDetails,
        ];
    }
    
    public function getTitle(): string
    {
        return 'Платежи: ' . '№'.$this->record->medicalHistory->number . ' - '. $this->record->patient->full_name;
    }

}

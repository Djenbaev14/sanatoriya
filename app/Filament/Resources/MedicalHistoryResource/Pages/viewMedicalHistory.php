<?php

namespace App\Filament\Resources\MedicalHistoryResource\Pages;

use App\Filament\Resources\MedicalHistoryResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\ViewEntry;
use Filament\Actions\Action;

class ViewMedicalHistory extends ViewRecord
{
    protected static string $resource = MedicalHistoryResource::class;
    protected static string $view = 'filament.pages.view-medical-history';

    public function getViewData(): array
    {
        return [
                            'medicalHistory' => $this->record,
                            'bedTotal' => $this->record->calculateBedCost(),
                            'mealTotal' => $this->record->calculateMealCost(),
                            'grandTotal' => $this->record->calculateBedCost() + $this->record->calculateMealCost(),
                            'days' => $this->record->calculateDays(),
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            Action::make('delete')
                ->label('Удалить')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Удалить запись')
                ->visible(fn () => $this->record->status_payment_id == 1)
                ->modalDescription('Вы уверены, что хотите удалить эту запись? Это действие нельзя отменить.')
                ->modalSubmitActionLabel('Да, удалить'),
                // ->action(fn () => $this->deleteRecord()),

            Action::make('edit')
                ->label('Редактировать')
                ->icon('heroicon-o-pencil')
                ->color('warning')
                ->url(fn () => route('filament.admin.resources.medical-histories.edit', $this->record)),

            Action::make('send_to_kassa')
                ->label('Отправить кассе')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Отправить в кассу')
                ->visible(fn () => $this->record->status_payment_id == 1)
                ->modalDescription('Отправить данные в кассу для оплаты?')
                ->modalSubmitActionLabel('Да, отправить')
                ->action(fn () => $this->sendToKassa()),
            Action::make('back')
                ->label('Назад')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->url(fn () => route('filament.admin.resources.patients.view', $this->record->patient_id)),
        ];
    }
    public function sendToKassa()
    {
        // Kassaga yuborish logikasi
        $this->record->update([
            'status_payment_id' => '2',
        ]);
        
        \Filament\Notifications\Notification::make()
            ->title('Отправлено в кассу')
            ->body('Данные успешно отправлены в кассу')
            ->success()
            ->send();
    }


}
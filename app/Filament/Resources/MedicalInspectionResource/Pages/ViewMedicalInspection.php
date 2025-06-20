<?php

namespace App\Filament\Resources\MedicalInspectionResource\Pages;

use App\Filament\Resources\MedicalInspectionResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
class ViewMedicalInspection extends ViewRecord
{
    protected static string $resource = MedicalInspectionResource::class;
    
    protected function getActions(): array
    {
        return [
            Action::make('delete')
                ->label('Удалить')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Удалить запись')
                ->modalDescription('Вы уверены, что хотите удалить эту запись? Это действие нельзя отменить.')
                ->modalSubmitActionLabel('Да, удалить')
                ->visible(fn () => $this->record->status_payment_id == 1)
                ->action(function () {
                    $patientId = $this->record->patient_id;

                    $this->record->inspectionDetails()->delete();
                    $this->record->delete();

                    Notification::make()
                        ->title('Запись успешно удалена')
                        ->success()
                        ->send();

                    return redirect()->to("/admin/patients/{$patientId}");
                }),

            Action::make('edit')
                ->label('Редактировать')
                ->icon('heroicon-o-pencil')
                ->color('warning')
                ->visible(fn () => ($this->record->status_payment_id != 3 || count($this->record->inspectionDetails) == 0))
                ->url(fn () => route('filament.admin.resources.medical-inspections.edit', $this->record)),
            Action::make('send_to_kassa')
                ->label('Отправить кассе')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Отправить в кассу')
                ->modalDescription('Отправить данные в кассу для оплаты?')
                ->modalSubmitActionLabel('Да, отправить')
                ->visible(fn () => $this->record->status_payment_id == 1)
                ->url(fn () => route('filament.admin.resources.patients.view', $this->record->patient_id))
                ->action(fn () => $this->sendToKassa()),
            Action::make('back')
                ->label('Назад')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->url(fn () => route('filament.admin.resources.patients.view', $this->record->patient_id)),


            // Action::make('print')
            //     ->label('Печать')
            //     ->icon('heroicon-o-printer')
            //     ->color('info')
            //     ->action(fn () => $this->printRecord()),
        ];
    }
    public function sendToKassa()
    {
        $this->record->update([
            'status_payment_id' => '2',
        ]);
        
        Notification::make()
            ->title('Отправлено в кассу')
            ->body('Данные успешно отправлены в кассу')
            ->success()
            ->send();
    }
}

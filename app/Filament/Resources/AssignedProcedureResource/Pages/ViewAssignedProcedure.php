<?php

namespace App\Filament\Resources\AssignedProcedureResource\Pages;

use App\Filament\Resources\AssignedProcedureResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Fieldset;
use Filament\Support\Enums\FontWeight;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
class ViewAssignedProcedure extends ViewRecord
{
    
    protected static string $resource = AssignedProcedureResource::class;
    // protected static string $view = 'filament.pages.view-lab-test-history';
    
    // public LabTestHistory $record;
    public $patient;
    public $labTestDetails;
    public $totalAmount = 0;

    // public function mount($record): void
    // {
    //     $this->record = AssignedProcedureResource::with([
    //         'patient', 
    //         'doctor', 
    //         'labTestDetails.lab_test'
    //     ])->findOrFail($record);
        
    //     $this->patient = $this->record->patient;
    //     $this->labTestDetails = $this->record->labTestDetails;
        
    //     // Umumiy summani hisoblash
    //     $this->totalAmount = $this->labTestDetails->sum(function ($detail) {
    //         return $detail->price * $detail->sessions;
    //     });
    // }
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
                ->action(fn () => $this->deleteRecord()),

            Action::make('edit')
                ->label('Редактировать')
                ->icon('heroicon-o-pencil')
                ->color('warning')
                ->visible(fn () => $this->record->status_payment_id == 1)
                ->url(fn () => route('filament.admin.resources.assigned-procedures.edit', $this->record)),

            Action::make('send_to_kassa')
                ->label('Отправить кассе')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Отправить в кассу')
                ->modalDescription('Отправить данные в кассу для оплаты?')
                ->modalSubmitActionLabel('Да, отправить')
                ->visible(fn () => $this->record->status_payment_id == 1)
                ->action(fn () => $this->sendToKassa()),
            Action::make('back')
                ->label('Назад')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->url(fn () => route('filament.admin.resources.medical-histories.view', $this->record->medical_history_id)),


            // Action::make('print')
            //     ->label('Печать')
            //     ->icon('heroicon-o-printer')
            //     ->color('info')
            //     ->action(fn () => $this->printRecord()),
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
    public function printRecord()
    {
        $this->dispatch('print-page');
        
        \Filament\Notifications\Notification::make()
            ->title('Подготовлено к печати')
            ->body('Документ готов к печати')
            ->success()
            ->send();
    }

}

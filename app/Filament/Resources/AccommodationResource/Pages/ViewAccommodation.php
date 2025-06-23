<?php

namespace App\Filament\Resources\AccommodationResource\Pages;

use App\Filament\Resources\AccommodationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewAccommodation extends ViewRecord
{
    protected static string $resource = AccommodationResource::class;
    
    protected static string $view = 'filament.pages.view-assigned-procedure';


    protected function getHeaderActions(): array
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
                ->visible(fn () => $this->record->status_payment_id == 1),
                // ->action(fn () => $this->deleteRecord()),

            Action::make('edit')
                ->label('Редактировать')
                ->icon('heroicon-o-pencil')
                ->color('warning')
                ->visible(fn () => $this->record->status_payment_id == 1)
                ->url(fn () => route('filament.admin.resources.lab-test-histories.edit', $this->record)),

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
            Action::make('print')
                ->label('Печать')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->action(function () {
                    // Add print functionality here
                    $this->js('window.print()');
                }),
        ];
    }
}

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

    public function getViewData(): array
    {
        return [
            'record' => $this->record,
            'totalAmount' => $this->record->calculateProceduresCost(),
        ];
    }
}

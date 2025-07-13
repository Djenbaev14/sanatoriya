<?php

namespace App\Filament\Resources\CasheBoxSessionResource\Pages;

use App\Filament\Resources\CasheBoxSessionResource;
use App\Models\BankTransfer;
use App\Models\Payment;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCasheBoxSession extends ViewRecord
{
    protected static string $resource = CasheBoxSessionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        $session = $this->record;
        $from = $session->opened_date;
        $to = $session->closed_date ?? now();

        $payments = Payment::where('payment_type_id', $session->payment_type_id)
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $bankTransfers = BankTransfer::where('payment_type_id', $session->payment_type_id)
            ->whereBetween('transferred_at', [$from, $to])
            ->get();

        return $infolist
            ->schema([
                Section::make('Касса')
                    ->schema([
                        TextEntry::make('opened_date')->label('Открытие'),
                        TextEntry::make('closed_date')->label('Закрытие'),
                    ]),

                // Section::make('Поступления в кассу')
                //     ->schema([
                //         TableEntry::make('Платежи')
                //             ->label('Платежи')
                //             ->records($payments)
                //             ->columns([
                //                 TextEntry::make('id')->label('ID'),
                //                 TextEntry::make('amount')->label('Сумма')->formatStateUsing(fn ($state) => number_format($state, 0, '.', ' ') . ' сум'),
                //                 TextEntry::make('created_at')->label('Дата'),
                //             ])
                //     ]),

                // Section::make('Сдано в банк')
                //     ->schema([
                //         TableEntry::make('Банк')
                //             ->label('Банк')
                //             ->records($bankTransfers)
                //             ->columns([
                //                 TextEntry::make('id')->label('ID'),
                //                 TextEntry::make('amount')->label('Сумма')->formatStateUsing(fn ($state) => number_format($state, 0, '.', ' ') . ' сум'),
                //                 TextEntry::make('transferred_at')->label('Дата передачи'),
                //             ])
                //     ]),
            ]);
    }
}

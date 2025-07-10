<?php

namespace App\Exports;

use App\Models\MedicalHistory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class DebtorMedicalHistoriesExport implements FromQuery, WithMapping, WithHeadings, WithEvents
{
    public function query()
    {
        return MedicalHistory::query()
            ->with('patient')
            ->get()
            ->filter(fn($item) => $item->getRemainingDebt() > 0);
    }

    public function map($record): array
    {
        return [
            $record->patient?->full_name,
            $record->getTotalCost(),
            $record->getTotalPaidAmount(),
            $record->getRemainingDebt(),
        ];
    }

    public function headings(): array
    {
        return ['ФИО', 'Общая сумма', 'Оплачено', 'Долг'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $row = $event->sheet->getHighestRow() + 1;

                $histories = MedicalHistory::all()->filter(fn ($item) => $item->getRemainingDebt() > 0);

                $total = $histories->sum(fn ($item) => $item->getTotalCost());
                $paid = $histories->sum(fn ($item) => $item->getTotalPaidAmount());
                $debt = $histories->sum(fn ($item) => $item->getRemainingDebt());

                $event->sheet->setCellValue("A{$row}", 'Итого:');
                $event->sheet->setCellValue("B{$row}", $total);
                $event->sheet->setCellValue("C{$row}", $paid);
                $event->sheet->setCellValue("D{$row}", $debt);
            }
        ];
    }
}
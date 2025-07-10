<?php

namespace App\Exports;

use App\Models\MedicalHistory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Events\AfterSheet;

class MedicalHistoriesExport implements FromCollection
{
     protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function collection()
    {
        return $this->records->map(function ($item) {
            return [
                'ФИО' => $item->patient->full_name,
                'Общая сумма' => $item->getTotalCost(),
                'Оплачено' => $item->getTotalPaidAmount(),
                'Долг' => $item->getRemainingDebt(),
            ];
        });
    }

    public function headings(): array
    {
        return ['ФИО', 'Общая сумма', 'Оплачено', 'Долг'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $row = count($this->records) + 2;

                $total = $this->records->sum(fn ($item) => $item->getTotalCost());
                $paid = $this->records->sum(fn ($item) => $item->getTotalPaidAmount());
                $debt = $this->records->sum(fn ($item) => $item->getRemainingDebt());

                $sheet->setCellValue("A$row", 'Общая сумма:');
                $sheet->setCellValue("B$row", $total);
                $sheet->setCellValue("C$row", $paid);
                $sheet->setCellValue("D$row", $debt);
            }
        ];
    }
}

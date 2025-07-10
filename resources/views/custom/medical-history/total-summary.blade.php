<x-filament::section heading="Общая сумма">
    @php
        $record = $getRecord();

        $total = 0;
        $total += $record->assignedProcedure?->getTotalCost() ?? 0;
        $total += $record->labTestHistory?->getTotalCost() ?? 0;
        $total += $record->accommodation?->getTotalCost() ?? 0;

        $paid = $record->getTotalPaid(); // Faqat to‘langanlar
        $debt = max($total - $record->getTotalPaidAndReturned(), 0); // Qarzdorlik (minus bo‘lsa 0 qilamiz)
    @endphp

    <div class="space-y-2">
        <p class="text-lg font-bold">
            Общая сумма: {{ number_format($total, 0, '.', ' ') }} сум
        </p>
        <p class="text-lg font-semibold text-green-600">
            Оплачено: {{ number_format($paid, 0, '.', ' ') }} сум
        </p>
        <p class="text-lg font-semibold text-red-600">
            Долг: {{ number_format($debt, 0, '.', ' ') }} сум
        </p>
    </div>
</x-filament::section>

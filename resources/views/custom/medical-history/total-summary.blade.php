<x-filament::section heading="Общая сумма">
    @php
        $record = $getRecord();

        $total = $record->getTotalCost() ?? 0; // Qo‘shimcha xarajatlar
        $paid = $record->getTotalPaidAmount(); // Faqat to‘langanlar
        $debt = $record->getRemainingDebt(); // Qarzdorlik (minus bo‘lsa 0 qilamiz)
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

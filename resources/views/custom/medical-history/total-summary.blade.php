<x-filament::section heading="Общая сумма">
    @php
    $record = $getRecord();
        $total = 0;
        $total += $record->assignedProcedure?->getTotalCost() ?? 0;
        $total += $record->labTestHistory?->getTotalCost() ?? 0;
        $total += $record->accommodation?->getTotalCost() ?? 0;
    @endphp

    <p class="text-lg font-bold">Общая сумма: {{ number_format($total, 0, '.', ' ') }} сум</p>
</x-filament::section>

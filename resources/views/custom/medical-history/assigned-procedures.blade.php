<x-filament::section heading="Процедуры">
    @php
    $record = $getRecord();
    $procedure = $record->assignedProcedure;
    @endphp

    @if($procedure && $procedure->procedureDetails->isNotEmpty())
        @php
            $details = $procedure->procedureDetails;
            $totalSum = $details->sum(fn($d) => $d->price * $d->sessions);
        @endphp
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-base font-semibold"></h3>
            <a
                href="{{ route('filament.admin.resources.assigned-procedures.edit', ['record' => $procedure->id]) }}"
                class="text-sm px-3 py-1 bg-primary-600 hover:bg-primary-700 text-white rounded transition"
            >
                 Редактировать
            </a>
        </div>
        <table style="width: 100%; border-collapse: collapse; table-layout: fixed;" >
            <thead>
                <tr class="bg-gray-100" style="white-space: nowrap">
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Процедура</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Сеансов</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Цена</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Сумма</th>
                </tr>
            </thead>
            <tbody>
                @foreach($procedure->procedureDetails as $detail)
                    <tr >
                        <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{ $detail->procedure->name ?? '—' }}</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{ $detail->sessions }}</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{ number_format($detail->price, 0, '.', ' ') }} сум</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">
                            {{ number_format($detail->sessions * $detail->price, 0, '.', ' ') }} сум
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="3" class="border px-2 py-1 font-bold text-right">Общий сумма:</td>
                    <td class="border px-2 py-1 font-bold text-left">
                        {{ number_format($totalSum, 0, '.', ' ') }} сум
                    </td>
                </tr>
            </tbody>
        </table>
    @else
        <p>Не назначено.</p>
    @endif
</x-filament::section>

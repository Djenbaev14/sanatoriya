<x-filament::section heading="Размещение (Койка)
">
    @php
    $record = $getRecord();
    $acc = $record->accommodation;
    @endphp

    @if($acc)
    @php
        $totalSum = $acc->tariff_price * $acc->calculateDays() + $acc->meal_price * $acc->calculateDays();
    @endphp
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-base font-semibold"></h3>
            <a
                href="{{ route('filament.admin.resources.accommodations.edit', ['record' => $acc->id]) }}"
                class="text-sm px-3 py-1 bg-primary-600 hover:bg-primary-700 text-white rounded transition"
            >
                 Редактировать
            </a>
        </div>
        <table style="width: 100%; border-collapse: collapse; table-layout: fixed;" >
            <thead>
                <tr class="bg-gray-100" style="white-space: nowrap">
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Наз</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">День</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Цена</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Сумма</th>
                </tr>
            </thead>
            <tbody>
                    <tr >
                        <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Койка</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{$acc->calculateDays()}}</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{ number_format($acc->tariff_price, 0, '.', ' ') }} сум</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{ number_format($acc->tariff_price * $acc->calculateDays(), 0, '.', ' ')  }} сум</td>
                    </tr>
                    <tr >
                        <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Питание</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{$acc->calculateDays()}}</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{ number_format($acc->meal_price, 0, '.', ' ') }} сум</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{ number_format($acc->meal_price *  $acc->calculateDays() , 0, '.', ' ') }} сум</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="border px-2 py-1 font-bold text-right">Общий сумма:</td>
                        <td class="border px-2 py-1 font-bold text-left">
                            {{ number_format($totalSum, 0, '.', ' ') }} сум
                        </td>
                    </tr>
            </tbody>
        </table>
    @else
        <p>Не размещён.</p>
    @endif
</x-filament::section>


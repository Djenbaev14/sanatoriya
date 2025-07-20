<x-filament::page>
    <h2 class="text-xl font-bold mb-4"></h2>

    {{-- Lab Test To'lovlari --}}
    <div class="mb-6">
        <h3 class="text-lg font-semibold mb-2"></h3>
        <table class="w-full border border-gray-300 text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border p-2">Название</th>
                    <th class="border p-2">Цена</th>
                    <th class="border p-2">Сессия</th>
                    <th class="border p-2">Сумма</th>
                </tr>
            </thead>
            <tbody>
                    @foreach ($procedureDetails as $detail)
                        <tr>
                            <td class="border p-2">{{ $detail['name'] ?? '-' }}</td>
                            <td class="border p-2">{{ number_format($detail['price'], 0, '.', ' ') }}</td>
                            <td class="border p-2">{{ $detail['sessions'] }}</td>
                            <td class="border p-2">{{ number_format($detail['price'] * $detail['sessions'], 0, '.', ' ') }}</td>
                        </tr>
                    @endforeach
                    @foreach ($labDetails as $detail)
                        <tr>
                            <td class="border p-2">{{ $detail['name'] ?? '-' }}</td>
                            <td class="border p-2">{{ number_format($detail['price'], 0, '.', ' ') }}</td>
                            <td class="border p-2">{{ $detail['sessions'] }}</td>
                            <td class="border p-2">{{ number_format($detail['price'] * $detail['sessions'], 0, '.', ' ') }}</td>
                        </tr>
                    @endforeach
                    @foreach ($accommodationDetails['main'] as $acc)
                        @if ($acc['meal_day'] > 0)
                            <tr>
                            <td class="border p-2">Питание</td>
                            <td class="border p-2">{{ number_format($acc['meal_price'],0, '.', ' ') }}</td>
                            <td class="border p-2">{{ $acc['meal_day'] }}</td>
                            <td class="border p-2">{{ number_format($acc['meal_price']*$acc['meal_day'], 0, '.', ' ') }}</td>
                        </tr>
                        @endif
                        @if ($acc['ward_day'] > 0)
                            <tr>
                                <td class="border p-2">Койка</td>
                                <td class="border p-2">{{ number_format($acc['tariff_price'],0, '.', ' ') }}</td>
                                <td class="border p-2">{{ $acc['ward_day'] }}</td>
                                <td class="border p-2">{{ number_format($acc['tariff_price'] *$acc['ward_day'], 0, '.', ' ') }}</td>
                            </tr>
                        @endif
                    @endforeach
                    @foreach ($accommodationDetails['partner'] as $acc)
                        @if ($acc['meal_day'] > 0)
                            <tr>
                            <td class="border p-2">Питание (Уход)</td>
                            <td class="border p-2">{{ number_format($acc['meal_price'],0, '.', ' ') }}</td>
                            <td class="border p-2">{{ $acc['meal_day'] }}</td>
                            <td class="border p-2">{{ number_format($acc['meal_price']*$acc['meal_day'], 0, '.', ' ') }}</td>
                        </tr>
                        @endif
                        @if ($acc['ward_day'] > 0)
                            <tr>
                                <td class="border p-2">Койка (Уход)</td>
                                <td class="border p-2">{{ number_format($acc['tariff_price'],0, '.', ' ') }}</td>
                                <td class="border p-2">{{ $acc['ward_day'] }}</td>
                                <td class="border p-2">{{ number_format($acc['tariff_price'] *$acc['ward_day'], 0, '.', ' ') }}</td>
                            </tr>
                        @endif
                    @endforeach
            </tbody>
        </table>
    </div>
    {{-- Umumiy To'lov --}}
    <div class="mt-8 text-right">
        <h2 class="text-xl font-bold">Итого оплачено: 
            {{ number_format($record->getTotalPaidAmount(), 0, '.', ' ') }} сум
        </h2>
    </div>
</x-filament::page>

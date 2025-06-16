{{-- resources/views/filament/modals/payments-history.blade.php --}}

<div class="space-y-4">
    {{-- Umumiy ma'lumotlar --}}
    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">Jami xarajat:</span>
                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                    {{ number_format($totalCost, 0, '.', ' ') }} сум
                </p>
            </div>
            <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">To'langan:</span>
                <p class="text-lg font-bold text-green-600 dark:text-green-400">
                    {{ number_format($totalPaid, 0, '.', ' ') }} сум
                </p>
            </div>
            <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">Qoldiq:</span>
                <p class="text-lg font-bold {{ $remaining > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                    {{ number_format($remaining, 0, '.', ' ') }} сум
                </p>
            </div>
        </div>
    </div>

    {{-- To'lovlar ro'yxati --}}
    @if($payments->count() > 0)
        <div class="overflow-hidden bg-white dark:bg-gray-900 shadow rounded-lg">
            <table class="divide-y divide-gray-200 dark:divide-gray-700" style="width:100%;">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Sana
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Miqdor
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Izoh
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($payments as $payment)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('d.m.Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 dark:text-green-400">
                                {{ number_format($payment->amount, 0, '.', ' ') }} сум
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $payment->description ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-8">
            <div class="text-gray-400 dark:text-gray-600 text-sm">
                Hozircha to'lovlar mavjud emas
            </div>
        </div>
    @endif
</div>
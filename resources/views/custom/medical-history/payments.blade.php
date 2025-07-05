<x-filament::section heading="">
    @php
    $record = $getRecord();
    $payments = $record->payments;
    @endphp
    @if($payments->isNotEmpty())
        <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
            <thead>
                <tr class="bg-gray-100" style="white-space: nowrap">
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Дата</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Сумма</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Способ оплаты</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                    <tr>
                        <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{ $payment->created_at->format('Y-m-d') }}</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{ number_format($payment->amount, 0, '.', ' ') }} сум</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{ $payment->paymentType->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Нет платежей.</p>
    @endif
</x-filament::section>
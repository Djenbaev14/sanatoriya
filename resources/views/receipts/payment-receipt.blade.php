{{-- resources/views/receipts/payment-receipt.blade.php --}}
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To'lov Kvitansiyasi</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .clinic-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .receipt-title {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
        }
        .receipt-number {
            font-size: 14px;
            margin-bottom: 10px;
        }
        .patient-info {
            background-color: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .patient-info h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            width: 150px;
            font-weight: bold;
        }
        .info-value {
            flex: 1;
        }
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .services-table th,
        .services-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .services-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .services-table .amount {
            text-align: right;
        }
        .total-section {
            border: 2px solid #000;
            padding: 15px;
            margin: 20px 0;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .total-row.final {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #000;
            padding-top: 8px;
        }
        .payments-section {
            margin-top: 30px;
        }
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .payments-table th,
        .payments-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .payments-table th {
            background-color: #e8f5e8;
            font-weight: bold;
        }
        .payments-table .amount {
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .signature-box {
            width: 200px;
            text-align: center;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
            height: 30px;
        }
        .remaining-amount {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            margin: 10px 0;
            font-weight: bold;
        }
        .remaining-amount.paid {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .remaining-amount.unpaid {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <div class="clinic-name">SANATORIYA NOMI</div>
        <div>Manzil: Toshkent shahar</div>
        <div>Tel: +998 XX XXX XX XX</div>
        
        <div class="receipt-title">TO'LOV KVITANSIYASI</div>
        <div class="receipt-number">№ {{ $medicalHistory->id }}/{{ $generatedAt->format('Y') }}</div>
        <div>Sana: {{ $generatedAt->format('d.m.Y H:i') }}</div>
    </div>

    {{-- Patient Information --}}
    <div class="patient-info">
        <h3>Пациент MA'LUMOTLARI</h3>
        <div class="info-row">
            <span class="info-label">F.I.Sh:</span>
            <span class="info-value">{{ $patient->full_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tug'ilgan sana:</span>
            <span class="info-value">{{ $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->format('d.m.Y') : '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Telefon:</span>
            <span class="info-value">{{ $patient->phone ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Kelgan sana:</span>
            <span class="info-value">{{ $medicalHistory->admission_date ? \Carbon\Carbon::parse($medicalHistory->admission_date)->format('d.m.Y') : '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Ketgan sana:</span>
            <span class="info-value">{{ $medicalHistory->discharge_date ? \Carbon\Carbon::parse($medicalHistory->discharge_date)->format('d.m.Y') : 'Hozirda davolanmoqda' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Yotgan kunlar:</span>
            <span class="info-value">{{ $medicalHistory->getDaysStayed() }} kun</span>
        </div>
    </div>

    {{-- Services Details --}}
    <h3>XIZMATLAR TAFSILOTI</h3>
    <table class="services-table">
        <thead>
            <tr>
                <th>Xizmat turi</th>
                <th class="amount">Jami</th>
            </tr>
        </thead>
        <tbody>
            @if($costs['procedures_cost'] > 0)
            <tr>
                <td>Protseduralar</td>
                <td class="amount">{{ number_format($costs['procedures_cost'], 0, '.', ' ') }} сум</td>
            </tr>
            @endif
            
            @if($costs['bed_cost'] > 0)
            <tr>
                <td>Palata ({{ $medicalHistory->getDaysStayed() }} kun)</td>
                {{-- <td>{{ $medicalHistory->getDaysStayed() }}</td>
                <td>{{ $medicalHistory->medicalBed && $medicalHistory->medicalBed->tariff ? number_format($medicalHistory->medicalBed->tariff->price, 0, '.', ' ') : '-' }} сум</td> --}}
                <td class="amount">{{ number_format($costs['bed_cost'], 0, '.', ' ') }} сум</td>
            </tr>
            @endif
            
            @if($costs['meal_cost'] > 0)
            <tr>
                <td>Ovqat ({{ $medicalHistory->getDaysStayed() }} kun)</td>
                {{-- <td>{{ $medicalHistory->getDaysStayed() }}</td>
                <td>{{ $medicalHistory->medicalMeal && $medicalHistory->medicalMeal->mealType ? number_format($medicalHistory->medicalMeal->mealType->price, 0, '.', ' ') : '-' }} сум</td> --}}
                <td class="amount">{{ number_format($costs['meal_cost'], 0, '.', ' ') }} сум</td>
            </tr>
            @endif
            
            @if($costs['lab_tests_cost'] > 0)
            <tr>
                <td>Laboratoriya tahlillari</td>
                {{-- <td>-</td>
                <td>-</td> --}}
                <td class="amount">{{ number_format($costs['lab_tests_cost'], 0, '.', ' ') }} сум</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Total Section --}}
    <div class="total-section">
        <div class="total-row">
            <span>Jami xarajat:</span>
            <span>{{ number_format($costs['total_cost'], 0, '.', ' ') }} сум</span>
        </div>
        <div class="total-row">
            <span>To'langan:</span>
            <span>{{ number_format($totalPaid, 0, '.', ' ') }} сум</span>
        </div>
        <div class="total-row final {{ $remaining <= 0 ? 'paid' : 'unpaid' }}">
            <span>{{ $remaining <= 0 ? 'To\'liq to\'langan' : 'Qoldiq:' }}</span>
            <span>{{ $remaining > 0 ? number_format($remaining, 0, '.', ' ') . ' сум' : '0 сум' }}</span>
        </div>
    </div>

    {{-- Payment Status --}}
    <div class="remaining-amount {{ $remaining <= 0 ? 'paid' : ($totalPaid > 0 ? '' : 'unpaid') }}">
        @if($remaining <= 0)
            ✓ BARCHA TO'LOVLAR AMALGA OSHIRILDI
        @elseif($totalPaid > 0)
            ⚠ QISMAN TO'LANGAN (Qoldiq: {{ number_format($remaining, 0, '.', ' ') }} сум)
        @else
            ✗ TO'LANMAGAN
        @endif
    </div>

    {{-- Payments History --}}
    @if($payments->count() > 0)
    <div class="payments-section">
        <h3>TO'LOVLAR TARIXI</h3>
        <table class="payments-table">
            <thead>
                <tr>
                    <th>№</th>
                    <th>Sana</th>
                    <th>Miqdor</th>
                    <th>Izoh</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $index => $payment)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d.m.Y') }}</td>
                    <td class="amount">{{ number_format($payment->amount, 0, '.', ' ') }} сум</td>
                    <td>{{ $payment->description ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background-color: #e8f5e8;">
                    <td colspan="2">JAMI:</td>
                    <td class="amount">{{ number_format($totalPaid, 0, '.', ' ') }} сум</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div>Kassir</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div>Bosh hisobchi</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div>Пациент/Vakil</div>
            </div>
        </div>
        
        <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666;">
            Ushbu kvitansiya {{ $generatedAt->format('d.m.Y H:i') }} da avtomatik tarzda yaratilgan.
        </div>
    </div>
</body>
</html>
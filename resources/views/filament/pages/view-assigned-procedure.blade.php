<x-filament-panels::page>
    <style>
        @media print {
            .fi-header, .fi-sidebar, .fi-navbar, .fi-footer {
                display: none !important;
            }
            
            .print-content {
                margin: 0;
                box-shadow: none;
            }
        }
        
        .procedure-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .procedure-table th,
        .procedure-table td {
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            text-align: left;
        }
        
        .procedure-table th {
            background-color: #f9fafb;
            font-weight: 600;
        }
        
        .total-row {
            background-color: #f0f9ff;
            font-weight: bold;
        }
        
        .header-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }
        
        .info-group h3 {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 15px;
            color: #1f2937;
        }
        
        .info-item {
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: 600;
            color: #374151;
        }
        
        .info-value {
            color: #1f2937;
        }
        
        .patient-name {
            color: #2563eb;
            font-weight: bold;
        }
        
        .footer-info {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 200px;
            margin-left: 10px;
        }
    </style>

    <div class="print-content bg-white p-8 rounded-lg shadow-sm">
        <!-- Header Section -->
        <div class="header-info">
            <div class="info-group">
                <h3>Пациент</h3>
                <div class="info-item">
                    <span class="info-label">ФИО:</span>
                    <span class="info-value patient-name">{{ $record->patient->full_name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Дата рождения:</span>
                    {{-- <span class="info-value">{{ $record->patient->birth_date ? $record->patient->birth_date->format('d.m.Y') : '' }}</span> --}}
                </div>
                <div class="info-item">
                    <span class="info-label">Телефон:</span>
                    <span class="info-value">{{ $record->patient->phone }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Адрес:</span>
                    <span class="info-value">{{ $record->patient->address }}</span>
                </div>
            </div>
            
            <div class="info-group">
                <h3>План</h3>
                <div class="info-item">
                    <span class="info-label">Номер:</span>
                    <span class="info-value">{{ $record->id }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Название:</span>
                    <span class="info-value">{{ $record->medicalHistory->diagnosis ?? 'ортодонтия' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Дата создания:</span>
                    <span class="info-value">{{ $record->created_at->format('Y-m-d H:i:s') }}</span>
                </div>
            </div>
        </div>

        <!-- Services Table -->
        <table class="procedure-table">
            <thead>
                <tr>
                    <th>Услуга</th>
                    <th>Кол-во</th>
                    <th>Цена</th>
                    {{-- <th>Доктор</th> --}}
                    <th>Сумма</th>
                </tr>
            </thead>
            <tbody>
                @foreach($record->procedureDetails as $detail)
                <tr>
                    <td>{{ $detail->procedure->name }}</td>
                    {{-- <td>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            Не указано
                        </span>
                    </td> --}}
                    <td>{{ $detail->sessions }}</td>
                    <td>{{ number_format($detail->price, 0, '.', ' ') }}</td>
                    {{-- <td>{{ $detail->doctor->name ?? '0.00' }}</td> --}}
                    <td>{{ number_format($detail->price * $detail->sessions, 0, '.', ' ') }}</td>
                </tr>
                @endforeach
                
                <!-- Total Row -->
                <tr class="total-row">
                    <td colspan="3" style="text-align: right; font-weight: bold;">Итого:</td>
                    <td style="font-weight: bold;">{{ number_format($totalAmount, 0, '.', ' ') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Information Section -->
        <div style="margin: 30px 0;">
            <div class="info-item">
                <span class="info-label">Информация:</span>
            </div>
            <div style="margin-top: 10px;">
                <span class="info-value">{{ $record->medicalHistory->diagnosis ?? 'Х м Хусенова М Врач Насриева Ю З,Д диск албом' }}</span>
            </div>
            <div style="margin-top: 10px;">
                <span class="info-label">Ассистент:</span>
            </div>
        </div>

        <!-- Footer Section -->
        <div class="footer-info">
            <div>
                <div style="font-weight: bold;">Umid Medical Centre</div>
                <div>Главный офис</div>
            </div>
            <div style="text-align: right;">
                <div>
                    <span class="info-label">Врач: Рентген</span>
                    <span class="signature-line"></span>
                </div>
            </div>
        </div>
    </div>

</x-filament-panels::page>
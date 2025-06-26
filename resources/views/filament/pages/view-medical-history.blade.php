{{-- resources/views/filament/infolists/medical-invoice.blade.php --}}

<x-filament-panels::page><div class="medical-invoice-wrapper">
    <style>
        .medical-invoice-wrapper {
            background: white;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #374151;
            line-height: 1.6;
        }

        .invoice-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .patient-info, .plan-info {
            background: white;
            padding: 1.25rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid #3b82f6;
            display: flex;
            align-items: center;
        }

        .section-header::before {
            content: '';
            width: 8px;
            height: 20px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 4px;
            margin-right: 0.75rem;
        }

        .info-item {
            display: flex;
            margin-bottom: 0.75rem;
            align-items: flex-start;
        }

        .info-label {
            font-weight: 600;
            color: #64748b;
            min-width: 130px;
            margin-right: 0.5rem;
            font-size: 0.875rem;
        }

        .info-value {
            color: #1e293b;
            flex: 1;
            font-weight: 500;
        }

        .patient-name {
            color: #3b82f6;
            font-weight: 700;
            font-size: 1.05rem;
        }

        .plan-number {
            color: #059669;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
        }

        .services-container {
            margin: 2rem 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .services-header {
            background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%);
            color: white;
            padding: 1rem 0;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .services-row {
            display: grid;
            grid-template-columns: 3fr 0.8fr 1fr 1fr 1.2fr;
            align-items: center;
            padding: 0 1.5rem;
            gap: 1rem;
        }

        .services-body .services-row {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.2s ease;
        }

        .services-body .services-row:hover {
            background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%);
            transform: translateX(2px);
        }

        .services-body .services-row:last-child {
            border-bottom: none;
        }

        .service-name {
            font-weight: 500;
            color: #1e293b;
            line-height: 1.4;
            padding: 0.25rem 0;
        }

        .service-category {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            padding: 0.875rem 1.5rem;
            font-weight: 700;
            color: #92400e;
            border-bottom: 2px solid #f59e0b;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .quantity, .price, .doctor, .total {
            text-align: center;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .price {
            font-family: 'JetBrains Mono', monospace;
            color: #059669;
            font-weight: 600;
        }

        .total {
            font-family: 'JetBrains Mono', monospace;
            color: #dc2626;
            font-weight: 700;
            background: #fef2f2;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        .additional-details {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .totals-summary {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 2px solid #10b981;
            border-radius: 12px;
            padding: 1.5rem;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            padding: 0.5rem 0;
            font-weight: 500;
        }

        .total-line.grand-total {
            border-top: 3px solid #10b981;
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 1.25rem;
            font-weight: 800;
            color: #065f46;
        }

        .grand-total .total-amount {
            font-family: 'JetBrains Mono', monospace;
            background: #10b981;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 1.1rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-emergency {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #dc2626;
            border: 1px solid #f87171;
        }

        .status-normal {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #059669;
            border: 1px solid #34d399;
        }

        .footer-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 2.5rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .medical-center-info {
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e293b;
        }

        .center-subtitle {
            color: #64748b;
            font-size: 0.9rem;
            margin-top: 0.25rem;
            font-weight: 500;
        }

        .signature-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
        }

        .signature-label {
            font-weight: 600;
            color: #475569;
        }

        .signature-line {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .doctor-name {
            font-weight: 600;
            color: #1e293b;
        }

        .signature-border {
            border-bottom: 2px solid #64748b;
            width: 150px;
        }

        @media print {
            .medical-invoice-wrapper {
                box-shadow: none;
                border: none;
            }
            
            .services-body .services-row:hover {
                background: none;
                transform: none;
            }
        }

        @media (max-width: 768px) {
            .invoice-header {
                grid-template-columns: 1fr;
            }

            .services-row {
                grid-template-columns: 2fr 1fr 1fr;
                font-size: 0.8rem;
            }

            .doctor, .price {
                display: none;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .footer-section {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }
    </style>

    @php
        // $data = $getState();
        $medicalHistory = $medicalHistory;
        // $treatmentTotal = $data['treatmentTotal'];
        $bedTotal = $bedTotal;
        $mealTotal = $mealTotal;
        $grandTotal = $grandTotal;
        $days = $days;
    @endphp

    <div class="medical-invoice-wrapper">
        <!-- Header Section -->
        <div class="invoice-header">
            <div class="patient-info">
                <h3 class="section-header">Пациент</h3>
                <div class="info-item">
                    <span class="info-label">ФИО:</span>
                    <span class="info-value patient-name">{{ $medicalHistory->patient->full_name ?? 'Не указано' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Дата рождения:</span>
                    <span class="info-value">{{ $medicalHistory->patient->birth_date ?? 'Не указано' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Телефон:</span>
                    <span class="info-value">{{ $medicalHistory->patient->phone ?? 'Не указано' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Адрес:</span>
                    <span class="info-value">{{ $medicalHistory->patient->address ?? 'Не указано' }}</span>
                </div>
            </div>

            <div class="plan-info">
                <h3 class="section-header">План</h3>
                <div class="info-item">
                    <span class="info-label">Номер:</span>
                    <span class="info-value plan-number">{{ str_pad($medicalHistory->number, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Дата создания:</span>
                    <span class="info-value">{{ $medicalHistory->created_at->format('Y-m-d H:i:s') }}</span>
                </div>
            </div>
        </div>


        <!-- Summary Section -->
        <div class="summary-grid">
            <div class="additional-details">
                <h3 class="section-header">Дополнительная информация</h3>
                
                <div class="info-item">
                    <span class="info-label">Ассистент:</span>
                    <span class="info-value">{{ $medicalHistory->doctor->name ?? 'Не указан' }}</span>
                </div>

                <div class="info-item">
                    <span class="info-label">Период лечения:</span>
                    <span class="info-value">
                        {{ $medicalHistory->admission_date?? 'Не указано' }} - 
                        {{ $medicalHistory->discharge_date ?? 'Продолжается' }}
                        ({{ $days }} дней)
                    </span>
                </div>

                @if($medicalHistory->side_effects)
                    <div class="info-item">
                        <span class="info-label">Побочные эффекты:</span>
                        <span class="info-value">{{ $medicalHistory->side_effects }}</span>
                    </div>
                @endif

                @if($medicalHistory->transport_type)
                    <div class="info-item">
                        <span class="info-label">Тип транспорта:</span>
                        <span class="info-value">
                            @switch($medicalHistory->transport_type)
                                @case('ambulance') 🚑 Скорая помощь @break
                                @case('family') 🚗 Семейный транспорт @break
                                @case('self') 🚶 Самостоятельно @break
                                @case('taxi') 🚕 Такси @break
                                @default {{ $medicalHistory->transport_type }}
                            @endswitch
                        </span>
                    </div>
                @endif

                @if($medicalHistory->referred_from)
                    <div class="info-item">
                        <span class="info-label">Направлен из:</span>
                        <span class="info-value">
                            @switch($medicalHistory->referred_from)
                                @case('clinic') 🏥 Поликлиника @break
                                @case('hospital') 🏨 Больница @break
                                @case('emergency') 🚨 Скорая помощь @break
                                @case('self') 👤 Самообращение @break
                                @default {{ $medicalHistory->referred_from }}
                            @endswitch
                        </span>
                    </div>
                @endif
            </div>

            <div class="totals-summary">
                {{-- @if($treatmentTotal > 0)
                    <div class="total-line">
                        <span>Медицинские услуги:</span>
                        <span>{{ number_format($treatmentTotal, 0, '.', ' ') }} сум</span>
                    </div>
                @endif --}}

                @if($bedTotal > 0)
                    <div class="total-line">
                        <span>Койко-места:</span>
                        <span>{{ number_format($medicalHistory->medicalBed->tariff->daily_price, 0, '.', ' ') }} сум * {{ $days }}</span>
                        <span>{{ number_format($bedTotal, 0, '.', ' ') }} сум</span>
                    </div>
                @endif

                @if($mealTotal > 0)
                    <div class="total-line">
                        <span>Питание:</span>
                        <span>{{ number_format($medicalHistory->medicalMeal->mealType->daily_price, 0, '.', ' ') }} сум * {{ $days }}</span>
                        <span>{{ number_format($mealTotal, 0, '.', ' ') }} сум</span>
                    </div>
                @endif

                <div class="total-line grand-total">
                    <span>Итого:</span>
                    <span class="total-amount">{{ number_format($grandTotal, 0, '.', ' ') }} сум</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-section">
            <div>
                <div class="medical-center-info">Нукус район реабилитации</div>
            </div>
            <div class="signature-area">
                <span class="signature-label">Врач:</span>
                <div class="signature-line">
                    <span class="doctor-name">{{ $medicalHistory->doctor->name ?? 'Admin' }}</span>
                    <div class="signature-border"></div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-filament-panels::page>

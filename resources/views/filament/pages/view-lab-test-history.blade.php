{{-- resources/views/filament/pages/view-lab-test-history.blade.php --}}

<x-filament-panels::page>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 print:shadow-none print:border-0">
        
        {{-- Header Section --}}
        <div class="flex justify-between items-start mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Пациент</h1>
                <div class="space-y-1 text-sm">
                    <div><span class="font-semibold">ФИО:</span> <a href="/admin/patients/{{$patient->id}}"><span style="color: #294590">{{ $patient->full_name }}</span></a></div>
                    <div><span class="font-semibold">Дата рождения:</span> {{ $patient->birth_date }}</div>
                    <div><span class="font-semibold">Телефон:</span> {{ $patient->phone }}</div>
                    <div><span class="font-semibold">Адрес:</span> {{ $patient->address }}</div>
                </div>
            </div>
            
            <div class="text-right">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">План</h2>
                <div class="space-y-1 text-sm">
                    <div><span class="font-semibold">Номер:</span> {{ $record->id }}</div>
                    <div><span class="font-semibold">Название:</span> {{ $record->medicalHistory->treatment_type ?? 'лабораторные анализы' }}</div>
                    <div><span class="font-semibold">Дата создания:</span> {{ $record->created_at->format('Y-m-d H:i:s') }}</div>
                </div>
            </div>
        </div>

        {{-- Services Table --}}
        <div style="margin: 20px 0 20px 0;">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 px-4 py-3 text-left font-semibold">Услуга</th>
                            {{-- <th class="border border-gray-300 px-4 py-3 text-center font-semibold">Зуб</th> --}}
                            <th class="border border-gray-300 px-4 py-3 text-center font-semibold">Кол-во</th>
                            <th class="border border-gray-300 px-4 py-3 text-center font-semibold">Цена</th>
                            <th class="border border-gray-300 px-4 py-3 text-center font-semibold">Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($labTestDetails as $detail)
                        <tr>
                            <td class="border border-gray-300 px-4 py-3">{{ $detail->lab_test->name }}</td>
                            {{-- <td class="border border-gray-300 px-4 py-3 text-center">
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Не указано</span>
                            </td> --}}
                            <td class="border border-gray-300 px-4 py-3 text-center">{{ $detail->sessions }}</td>
                            <td class="border border-gray-300 px-4 py-3 text-right">{{ number_format($detail->price, 2, '.', ' ') }}</td>
                            <td class="border border-gray-300 px-4 py-3 text-right font-semibold">{{ number_format($detail->price * $detail->sessions, 2, '.', ' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{-- Total Amount --}}
            <div class="mt-4 flex justify-end">
                    <span class="text-sm font-bold">Итого: {{ number_format($totalAmount, 2, '.', ' ') }}</span>
            </div>
        </div>
        {{-- Footer --}}
        <div class="flex justify-between items-end pt-8">
            <div>
                <div class="font-semibold text-gray-900">Nokis Rayon Reabilizatsiya orayi</div>
                <div class="text-sm text-gray-600">Главный офис</div>
            </div>
            <div class="text-right">
                <div class="font-semibold text-gray-900">Врач: {{ $record->doctor->name }} __________</div>
            </div>
        </div>

        {{-- Print Button --}}
        <div class="mt-6 flex justify-end print:hidden">
            <button onclick="window.print()" 
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                Распечатать
            </button>
        </div>
    </div>

    {{-- Print Styles --}}
    <style>
        @media print {
            body { font-size: 12px; }
            .filament-main { margin: 0; padding: 0; }
            .bg-white { background: white !important; }
        }
    </style>
</x-filament-panels::page>
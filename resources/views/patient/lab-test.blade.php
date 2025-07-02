<div class="w-full">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Анализи</h2>
        
        {{-- <a 
            href="/admin/lab-test-histories/create?patient_id={{$patient->id}}" 
            class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition"
        >
            + Добавить Анализ
        </a> --}}
    </div>
    <div style="width: 100%; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
            <thead>
                <tr class="bg-gray-100" style="white-space: nowrap">
                    <th style="border: 1px solid #d1d5db; padding: 12px;">История болезно</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px;">Доктор</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px;">Статус</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px;">Сумма</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px;">Дата создания</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px;">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($labTestHistories as $key => $history)
                        <tr style="border-bottom:1px solid #929292;white-space: nowrap;">
                    <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{str_pad('№'.$history->medical_history->number, 10) }}</td>
                            <td style="border: 1px solid #d1d5db; padding: 12px;">{{ $history->doctor->name}}</td>
                            <td style="border: 1px solid #d1d5db; padding: 12px;">{{ $history->statusPayment->name}}</td>
                            <td style="border: 1px solid #d1d5db; padding: 12px;">{{ number_format($history->labTestDetails->sum('price')) .' сум'}}</td>
                            <td style="border: 1px solid #d1d5db; padding: 12px;">{{ $history->created_at}}</td>
                            <td style="border: 1px solid #d1d5db; padding: 12px;">
                                <a href="/admin/lab-test-histories/{{$history->id}}" style="color: #094ecd">Просмотр</a>
                                <a href="/admin/lab-test-histories/{{$history->id}}/edit" style="color: #cd0909;margin-left:10px">Редактировать</a>
                            </td>
                        </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
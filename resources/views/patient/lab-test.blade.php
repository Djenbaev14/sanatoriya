<div class="w-full">
    <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">Анализи</h2>
    
    <a 
        href="/admin/lab-test-histories/create?patient_id={{$patient->id}}" 
        class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition"
    >
        + Добавить Анализ
    </a>
</div>
<table class="w-100 border-collapse border border-gray-300 " style="width: 100%;font-size:12px">
    <thead>
        <tr class="bg-gray-100" style="white-space: nowrap">
            <th class="border border-gray-300 px-4 py-2">ID</th>
            <th class="border border-gray-300 px-4 py-2">Название</th>
            <th class="border border-gray-300 px-4 py-2">Доктор</th>
            <th class="border border-gray-300 px-4 py-2">Статус</th>
            <th class="border border-gray-300 px-4 py-2">Сумма</th>
            <th class="border border-gray-300 px-4 py-2">Дата создания</th>
            <th class="border border-gray-300 px-4 py-2">Действия</th>
        </tr>
    </thead>
    <tbody>
        @foreach($labTestHistories as $key => $history)
                <tr style="border-bottom:1px solid #929292;white-space: nowrap;">
                    <td class="border-gray-300 p-2">{{ $history->id}}</td>
                    <td class="border-gray-300 p-2"></td>
                    <td class="border-gray-300 p-2">{{ $history->doctor->name}}</td>
                    <td class="border-gray-300 p-2">{{ $history->statusPayment->name}}</td>
                    <td class="border-gray-300 p-2">{{ number_format($history->labTestDetails->sum('price')) .' сум'}}</td>
                    <td class="border-gray-300 p-2">{{ $history->created_at}}</td>
                    <td class="border-gray-300 p-2"><a href="/admin/lab-test-histories/{{$history->id}}" style="color: #094ecd">Просмотр</a></td>
                </tr>
        @endforeach
    </tbody>
</table>

</div>
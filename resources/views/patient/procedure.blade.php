<div class="w-full">
    <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">Личение</h2>
    
    <a 
        href="/admin/assigned-procedures/create?patient_id={{$patient->id}}" 
        class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition"
    >
        + Добавить Личение
    </a>
</div>
<table class=" border-collapse border border-gray-300 " style="width:100%;font-size:12px">
    <thead>
        <tr class="bg-gray-100" style="white-space: nowrap">
            <th class="border border-gray-300 px-4 py-2">ID</th>
            <th class="border border-gray-300 px-4 py-2">Название</th>
            <th class="border border-gray-300 px-4 py-2">Статус</th>
            <th class="border border-gray-300 px-4 py-2">Сумма</th>
            <th class="border border-gray-300 px-4 py-2">Дата создания</th>
            <th class="border border-gray-300 px-4 py-2">Действия</th>
        </tr>
    </thead>
    <tbody>
        @foreach($assignedProcedures as $key => $procedure)
                <tr style="border-bottom:1px solid #929292;white-space: nowrap;">
                    <td class="border-gray-300 p-2">{{ $procedure->id}}</td>
                    <td class="border-gray-300 p-2"></td>
                    <td class="border-gray-300 p-2">{{ $procedure->statusPayment->name}}</td>
                    <td class="border-gray-300 p-2">{{ number_format($procedure->getTotalCost()) .' сум'}}</td>
                    <td class="border-gray-300 p-2">{{ $procedure->created_at}}</td>
                    <td class="border-gray-300 p-2"><a href="/admin/lab-test-histories/{{$procedure->id}}" style="color: #094ecd">Просмотр</a></td>
                </tr>
        @endforeach
    </tbody>
</table>

</div>
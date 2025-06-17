<div class="w-full">
    <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">Осмотр</h2>
    
    <a 
        href="/admin/medical-histories/create?patient_id={{$patient->id}}" 
        class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition"
    >
        + Добавить Осмотр
    </a>
</div>
<table class="w-100 border-collapse border border-gray-300 " style="width: 100%;font-size:12px">
    <thead>
        <tr class="bg-gray-100" style="white-space: nowrap">
            <th class="border border-gray-300 px-4 py-2">ID</th>
            <th class="border border-gray-300 px-4 py-2">Название</th>
            <th class="border border-gray-300 px-4 py-2">сумма</th>
            <th class="border border-gray-300 px-4 py-2">Врач осмотра</th>
            <th class="border border-gray-300 px-4 py-2">Дата создания</th>
        </tr>
    </thead>
    <tbody>
        @foreach($medicalHistories as $key => $history)
        <tr style="border-bottom:1px solid #929292;white-space: nowrap;">
            <td class="border-gray-300 p-2">{{ $history->id}}</td>
            <td class="border-gray-300 p-2">Осмотр</td>
            <td class="border-gray-300 p-2">{{number_format($history->medicalInspection->getTotalCost())}} сум</td>
            <td class="border-gray-300 p-2">{{ $history->doctor->name}}</td>
            <td class="border-gray-300 p-2">{{ $history->created_at}}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</div>
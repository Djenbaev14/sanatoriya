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
    <div style="width: 100%; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; table-layout: fixed;" >
            <thead>
                <tr class="bg-gray-100" style="white-space: nowrap">
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">ID</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Статус</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Сумма</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Дата создания</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignedProcedures as $key => $procedure)
                        <tr style="border-bottom:1px solid #929292;white-space: nowrap;">
                            <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{ $procedure->id}}</td>
                            <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{ $procedure->statusPayment->name}}</td>
                            <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{ number_format($procedure->getTotalCost()) .' сум'}}</td>
                            <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{ $procedure->created_at}}</td>
                            <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;"><a href="/admin/assigned-procedures/{{$procedure->id}}" style="color: #094ecd">Просмотр</a>
                           <a href="/admin/returned-procedures/create?assigned-procedure={{$procedure->id}}" style="color: #dd0c0c;margin-left:15px;">Возврат</a></td>
                        </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
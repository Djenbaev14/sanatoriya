<div class="w-full overflow-x-auto">
    <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">Приемный Осмотр</h2>
    
    {{-- <a 
        href="/admin/medical-inspections/create?patient_id={{$patient->id}}" 
        class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition"
    >
        + Добавить Осмотр
    </a> --}}
    </div>
    <div style="width: 100%; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; table-layout: fixed;" >
            <thead>
                <tr class="bg-gray-100" style="white-space: nowrap">
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">История болезно</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Приемный Врач</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Дата создания</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($medicalInspections as $key => $inspection)
                <tr style="border-bottom:1px solid #929292;white-space: nowrap;">
                    <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{str_pad('№'.$inspection->medicalHistory->number, 10) }}</td>
                    <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{ $inspection->initialDoctor->name}}</td>
                    <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{ $inspection->created_at}}</td>
                    <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">
                        <a href="{{route('filament.admin.resources.medical-inspections.view', $inspection->id)}}" style="color: #094ecd">Просмотр</a>
                        <a href="{{route('download.inspection', $inspection->id)}}" style="color: green;margin-left: 16px;" target="_blank">Скачать</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

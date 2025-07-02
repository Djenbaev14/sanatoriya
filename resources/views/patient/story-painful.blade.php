<div style="width: 100%; display: block;">
    <div class="flex justify-between items-center mb-4" style="width: 100%;">
        <h2 class="text-xl font-bold">История болезно</h2>
        
        {{-- can access --}}
        {{-- @can('создать историю болезни', \App\Models\MedicalHistory::class)
            <a 
                href="{{ route('filament.admin.resources.medical-histories.create', ['patient_id' => $patient->id]) }}" 
                class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition"
            >
                + Добавить История
            </a>
        @endcan --}}

    </div>
    
    <!-- Force full width with inline styles -->
    <div style="width: 100%; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
            <thead>
                <tr style="background-color: #f3f4f6;">
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">№</th>
                    {{-- <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Дата поступления</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Дата выписки</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">День</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Койка и питание сумма</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Статус</th> --}}
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Дата создания</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px;">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($medicalHistories as $key => $history)
                <tr style="border-bottom: 1px solid #929292;">
                    <td style="border: 1px solid #d1d5db; padding: 12px;">{{ $history->number }}</td>
                    {{-- <td style="border: 1px solid #d1d5db; padding: 12px;">{{ $history->admission_date }}</td>
                    <td style="border: 1px solid #d1d5db; padding: 12px;">{{ $history->discharge_date }}</td>
                    <td style="border: 1px solid #d1d5db; padding: 12px;">{{ $history->calculateDays() }} день</td>
                    <td style="border: 1px solid #d1d5db; padding: 12px;">{{ number_format($history->getTotalCost()) }} сум</td>
                    <td style="border: 1px solid #d1d5db; padding: 12px;">{{ $history->BedMealstatusPayment->name }}</td> --}}
                    <td style="border: 1px solid #d1d5db; padding: 12px;">{{ $history->created_at }}</td>
                    <td style="border: 1px solid #d1d5db; padding: 12px;"><a href="/admin/medical-histories/{{$history->id}}" style="color: #094ecd">Просмотр</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
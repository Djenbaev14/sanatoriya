{{-- <div style="width: 100%; display: block;">
    <div class="flex justify-between items-center mb-4" style="width: 100%;">
        <h2 class="text-xl font-bold">Условия размещения</h2>
    </div>
    <div style="width: 100%; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
            <thead>
                <tr style="background-color: #f3f4f6;">
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">История болезно</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Дата поступления</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Дата выписки</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">День</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Койка и питание сумма</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Статус</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">Дата создания</th>
                    <th style="border: 1px solid #d1d5db; padding: 12px;">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($accommodations as $key => $accommodation)
                    <tr style="border-bottom: 1px solid #929292;">
                        <td style="border: 1px solid #d1d5db; padding: 12px; text-align: left;">{{str_pad('№'.$accommodation->medicalHistory->number, 10) }}</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px;">{{ $accommodation->admission_date }}</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px;">{{ $accommodation->discharge_date }}</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px;">{{ $accommodation->calculateDays() }} день</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px;">{{ number_format($accommodation->getTotalCost()) }} сум</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px;">{{ $accommodation->statusPayment->name }}</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px;">{{ $accommodation->created_at }}</td>
                        <td style="border: 1px solid #d1d5db; padding: 12px;">
                            <a href="/admin/accommodations/{{$accommodation->id}}/edit" style="color: #cd0909">Редактировать</a>
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
</div> --}}
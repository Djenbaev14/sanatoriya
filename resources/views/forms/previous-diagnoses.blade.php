@php
    $doctorId = auth()->id();
        
    $diagnoses = \App\Models\DepartmentInspection::where('assigned_doctor_id', $doctorId)
        ->whereNotNull('admission_diagnosis')
        ->latest()
        ->get()
        ->pluck('admission_diagnosis')
        ->unique() // bir xillarini olib tashlaydi
        ->take(3)
        ->values(); // indexlarni tiklaydi
@endphp

@if ($diagnoses->isNotEmpty())
    <div class="space-y-2 mb-2">
        <label class="text-sm font-medium text-gray-700">📝 Предыдущие диагнозы:</label>
        <div class="flex flex-wrap gap-2">
            @foreach ($diagnoses as $diag)
                <button
                    type="button"
                    onclick="addDiagnosis(`{{ addslashes($diag) }}`)"
                    class="px-3 py-1 bg-gray-100 text-sm rounded hover:bg-gray-200 border border-gray-300"
                >
                    {{ \Str::limit($diag,  200) }}
                </button>
            @endforeach
        </div>
    </div>
    <script>
        function addDiagnosis(text) {
            const textarea = document.querySelector('[name="data.admission_diagnosis"]') || document.querySelector('[wire\\:model="data.admission_diagnosis"]');
            
            if (textarea) {
                textarea.value = textarea.value
                    ? textarea.value.trim() + "\n" + text
                    : text;

                // Livewirega signal berish
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
            } else {
                console.warn('Textarea not found');
            }
        }
    </script>
@endif

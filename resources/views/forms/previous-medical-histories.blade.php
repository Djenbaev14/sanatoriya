@php
    $doctorId = auth()->id();
        
    $medicalHistories = \App\Models\DepartmentInspection::where('assigned_doctor_id', $doctorId)
        ->whereNotNull('medical_history')
        ->latest()
        ->get()
        ->pluck('medical_history')
        ->unique() // bir xillarini olib tashlaydi
        ->take(3)
        ->values(); // indexlarni tiklaydi
@endphp

@if ($medicalHistories->isNotEmpty())
    <div class="space-y-2 mb-2">
        <label class="text-sm font-medium text-gray-700">📝 Предыдущие АНАМНЕЗ МОРБИ:</label>
        <div class="flex flex-wrap gap-2">
            @foreach ($medicalHistories as $medical)
                <button
                    type="button"
                    onclick="addMedical(`{{ addslashes($medical) }}`)"
                    class="px-3 py-1 bg-gray-100 text-sm rounded hover:bg-gray-200 border border-gray-300"
                >
                    {{ \Str::limit($medical,  200) }}
                </button>
            @endforeach
        </div>
    </div>
    <script>
        function addMedical(text) {
            const textarea = document.querySelector('[name="data.medical_history"]') || document.querySelector('[wire\\:model="data.medical_history"]');
            
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

@php
    $doctorId = auth()->id();
        
    $objectivelies = \App\Models\DepartmentInspection::where('assigned_doctor_id', $doctorId)
        ->whereNotNull('objectively')
        ->latest()
        ->get()
        ->pluck('objectively')
        ->unique() // bir xillarini olib tashlaydi
        ->take(3)
        ->values(); // indexlarni tiklaydi
@endphp

@if ($objectivelies->isNotEmpty())
    <div class="space-y-2 mb-2">
        <label class="text-sm font-medium text-gray-700">📝 Предыдущие СТАТУС ПРЕДСТАВЛЯЕТ ЦЕЛЬ:</label>
        <div class="flex flex-wrap gap-2">
            @foreach ($objectivelies as $obj)
                <button
                    type="button"
                    onclick="addObject(`{{ addslashes($obj) }}`)"
                    class="px-3 py-1 bg-gray-100 text-sm rounded hover:bg-gray-200 border border-gray-300"
                >
                    {{ \Str::limit($obj,  200) }}
                </button>
            @endforeach
        </div>
    </div>
    <script>
        function addObject(text) {
            const textarea = document.querySelector('[name="data.objectively"]') || document.querySelector('[wire\\:model="data.objectively"]');
            
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

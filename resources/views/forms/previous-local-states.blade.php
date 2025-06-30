@php
    $doctorId = auth()->id();
        
    $localStates = \App\Models\DepartmentInspection::where('assigned_doctor_id', $doctorId)
        ->whereNotNull('local_state')
        ->latest()
        ->get()
        ->pluck('local_state')
        ->unique() // bir xillarini olib tashlaydi
        ->take(3)
        ->values(); // indexlarni tiklaydi
@endphp

@if ($localStates->isNotEmpty())
    <div class="space-y-2 mb-2">
        <label class="text-sm font-medium text-gray-700">📝 Предыдущие ЛОКАЛЬНЫЕ СИТУАЦИИ:</label>
        <div class="flex flex-wrap gap-2">
            @foreach ($localStates as $state)
                <button
                    type="button"
                    onclick="addState(`{{ addslashes($state) }}`)"
                    class="px-3 py-1 bg-gray-100 text-sm rounded hover:bg-gray-200 border border-gray-300"
                >
                    {{ \Str::limit($state,  200) }}
                </button>
            @endforeach
        </div>
    </div>
    <script>
        function addState(text) {
            const textarea = document.querySelector('[name="data.local_state"]') || document.querySelector('[wire\\:model="data.local_state"]');
            
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

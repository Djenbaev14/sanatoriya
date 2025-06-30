@php
    $doctorId = auth()->id();
        
    $histories = \App\Models\DepartmentInspection::where('assigned_doctor_id', $doctorId)
        ->whereNotNull('epidemiological_history')
        ->latest()
        ->get()
        ->pluck('epidemiological_history')
        ->unique() // bir xillarini olib tashlaydi
        ->take(3)
        ->values(); // indexlarni tiklaydi
@endphp

@if ($histories->isNotEmpty())
    <div class="space-y-2 mb-2">
        <label class="text-sm font-medium text-gray-700">📝 Предыдущие Эпидемиологический анамнез:</label>
        <div class="flex flex-wrap gap-2">
            @foreach ($histories as $history)
                <button
                    type="button"
                    onclick="addHistory(`{{ addslashes($history) }}`)"
                    class="px-3 py-1 bg-gray-100 text-sm rounded hover:bg-gray-200 border border-gray-300"
                >
                    {{ \Str::limit($history,  200) }}
                </button>
            @endforeach
        </div>
    </div>
    <script>
        function addHistory(text) {
            const textarea = document.querySelector('[name="data.epidemiological_history"]') || document.querySelector('[wire\\:model="data.epidemiological_history"]');
            
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

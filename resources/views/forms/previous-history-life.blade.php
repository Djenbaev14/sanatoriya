@php
    $doctorId = auth()->id();
        
    $histories = \App\Models\DepartmentInspection::where('assigned_doctor_id', $doctorId)
        ->whereNotNull('history_life')
        ->latest()
        ->get()
        ->pluck('history_life')
        ->unique() // bir xillarini olib tashlaydi
        ->take(3)
        ->values(); // indexlarni tiklaydi
@endphp

@if ($histories->isNotEmpty())
    <div class="space-y-2 mb-2">
        <label class="text-sm font-medium text-gray-700">📝 Предыдущие ЖИЗНЕННЫЙ АНАМНЕЗ:</label>
        <div class="flex flex-wrap gap-2">
            @foreach ($histories as $history)
                <button
                    type="button"
                    onclick="addLife(`{{ addslashes($history) }}`)"
                    class="px-3 py-1 bg-gray-100 text-sm rounded hover:bg-gray-200 border border-gray-300"
                >
                    {{ \Str::limit($history,  200) }}
                </button>
            @endforeach
        </div>
    </div>
    <script>
        function addLife(text) {
            const textarea = document.querySelector('[name="data.history_life"]') || document.querySelector('[wire\\:model="data.history_life"]');
            
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

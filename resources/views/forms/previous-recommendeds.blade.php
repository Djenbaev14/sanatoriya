@php
    $doctorId = auth()->id();
        
    $recommendeds = \App\Models\DepartmentInspection::where('assigned_doctor_id', $doctorId)
        ->whereNotNull('recommended')
        ->latest()
        ->get()
        ->pluck('recommended')
        ->unique() // bir xillarini olib tashlaydi
        ->take(3)
        ->values(); // indexlarni tiklaydi
@endphp

@if ($recommendeds->isNotEmpty())
    <div class="space-y-2 mb-2">
        <label class="text-sm font-medium text-gray-700">📝 Предыдущие Рекомендации:</label>
        <div class="flex flex-wrap gap-2">
            @foreach ($recommendeds as $recom)
                <button
                    type="button"
                    onclick="addRecommended(`{{ addslashes($recom) }}`)"
                    class="px-3 py-1 bg-gray-100 text-sm rounded hover:bg-gray-200 border border-gray-300"
                >
                    {{ \Str::limit($recom,  200) }}
                </button>
            @endforeach
        </div>
    </div>
    <script>
        function addRecommended(text) {
            const textarea = document.querySelector('[name="data.recommended"]') || document.querySelector('[wire\\:model="data.recommended"]');
            
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

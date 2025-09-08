<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            photo: @entangle($getStatePath()),
            stream: null,
            startCamera() {
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(s => { 
                        this.stream = s;
                        this.$refs.video.srcObject = s;
                    })
                    .catch(e => console.error(e));
            },
            capture() {
                const video = this.$refs.video;
                const canvas = this.$refs.canvas;
                const context = canvas.getContext('2d');
                
                // Video oqimini canvasga chizish
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                // Base64 PNG qilib olish
                this.photo = canvas.toDataURL('image/png');
            }
        }"
        x-init="startCamera()"
        class="space-y-2"
    >
        {{-- Kamera oqimi --}}
        <video x-ref="video" autoplay playsinline width="320" height="240" class="rounded border"></video>

        {{-- Canvas (rasmni olish uchun) --}}
        <canvas x-ref="canvas" width="320" height="240" class="hidden"></canvas>

        {{-- Tugmalar --}}
        <div class="flex items-center gap-2">
            <x-filament::button type="button" color="primary" x-on:click="capture">
                📸 Rasm olish
            </x-filament::button>
        </div>

        {{-- Olingan rasm preview --}}
        <template x-if="photo">
            <img :src="photo" class="mt-2 rounded border" width="160" />
        </template>
    </div>
</x-dynamic-component>

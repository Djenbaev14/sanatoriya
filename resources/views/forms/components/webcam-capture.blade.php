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
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                this.photo = canvas.toDataURL('image/png');
            }
        }"
        x-init="startCamera()"
        class="space-y-2"
    >
        {{-- Kamera oqimi --}}
        <video 
            x-ref="video" 
            autoplay 
            playsinline 
            class="rounded border w-full aspect-video object-cover"
        ></video>

        {{-- Canvas (rasm olish uchun, yashirin) --}}
        <canvas 
            x-ref="canvas" 
            class="hidden w-full aspect-video"
        ></canvas>

        {{-- Tugmalar --}}
        <div class="flex items-center gap-2">
            <x-filament::button type="button" color="primary" x-on:click="capture">
                📸 Rasm olish
            </x-filament::button>
        </div>

        {{-- Olingan rasm preview --}}
        <template x-if="photo">
            <img :src="photo" class="mt-2 rounded border w-full aspect-video object-cover" />
        </template>
    </div>
</x-dynamic-component>

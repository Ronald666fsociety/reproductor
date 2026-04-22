<script setup lang="ts">
import { ref } from 'vue';
import { Download, Music, File as FileIcon, Search, ZoomIn, ZoomOut, RotateCw } from 'lucide-vue-next';
import VideoPlayer from './VideoPlayer.vue';

const props = defineProps<{
    file: {
        id: number;
        title: string;
        path: string;
        file_type: string | null;
    };
    streamUrl: string;
}>();

const imageZoom = ref(1);
const imageRotation = ref(0);

const zoomIn = () => { if (imageZoom.value < 3) imageZoom.value += 0.2; };
const zoomOut = () => { if (imageZoom.value > 0.5) imageZoom.value -= 0.2; };
const rotate = () => { imageRotation.value = (imageRotation.value + 90) % 360; };

const isImage = (type: string | null) => type?.startsWith('image/');
const isVideo = (type: string | null) => type?.startsWith('video/');
const isAudio = (type: string | null) => type?.startsWith('audio/');
const isPdf = (type: string | null) => type === 'application/pdf';

</script>

<template>
    <div class="universal-viewer w-full h-full relative">
        
        <!-- VIDEO VIEWER -->
        <VideoPlayer 
            v-if="isVideo(file.file_type)"
            :key="file.id"
            :src="streamUrl"
            :title="file.title"
        />

        <!-- IMAGE VIEWER -->
        <div v-else-if="isImage(file.file_type)" class="relative w-full aspect-video bg-black rounded-3xl overflow-hidden border border-white/10 flex items-center justify-center p-4">
            <div class="absolute top-4 right-4 z-20 flex gap-2">
                <button @click="zoomIn" class="p-2 bg-white/10 hover:bg-white/20 rounded-lg text-white backdrop-blur-md transition"><ZoomIn class="w-4 h-4" /></button>
                <button @click="zoomOut" class="p-2 bg-white/10 hover:bg-white/20 rounded-lg text-white backdrop-blur-md transition"><ZoomOut class="w-4 h-4" /></button>
                <button @click="rotate" class="p-2 bg-white/10 hover:bg-white/20 rounded-lg text-white backdrop-blur-md transition"><RotateCw class="w-4 h-4" /></button>
            </div>
            <div class="w-full h-full flex items-center justify-center overflow-hidden">
                <img 
                    :src="streamUrl" 
                    class="max-w-full max-h-full object-contain transition-transform duration-300 shadow-2xl"
                    :style="{ transform: `scale(${imageZoom}) rotate(${imageRotation}deg)` }"
                />
            </div>
        </div>

        <!-- AUDIO VIEWER -->
        <div v-else-if="isAudio(file.file_type)" class="w-full aspect-video bg-gradient-to-br from-indigo-900 to-black rounded-3xl overflow-hidden border border-white/10 flex flex-col items-center justify-center p-12 text-center">
            <div class="w-32 h-32 bg-indigo-500/20 rounded-full flex items-center justify-center mb-8 relative">
                <div class="absolute inset-0 border-4 border-indigo-500 rounded-full animate-[spin_4s_linear_infinite] border-t-transparent"></div>
                <Music class="w-16 h-16 text-indigo-400" />
            </div>
            <h3 class="text-3xl font-black text-white mb-2">{{ file.title }}</h3>
            <p class="text-white/40 mb-8 font-mono text-xs uppercase tracking-widest">Reproduciendo Audio</p>
            <audio controls :src="streamUrl" class="w-full max-w-lg mt-4 outline-none filter invert contrast-125"></audio>
        </div>

        <!-- PDF VIEWER -->
        <div v-else-if="isPdf(file.file_type)" class="w-full aspect-video bg-gray-900 rounded-3xl overflow-hidden border border-white/10 relative">
            <div class="absolute top-4 right-4 z-10">
                <a :href="streamUrl" download target="_blank" class="p-3 bg-pink-600/80 hover:bg-pink-600 text-white rounded-xl backdrop-blur-md transition flex gap-2 items-center text-xs font-bold shadow-lg">
                    <Download class="w-4 h-4"/> DESCARGAR PDF
                </a>
            </div>
            <iframe :src="streamUrl + '#toolbar=0'" class="w-full h-full border-0"></iframe>
        </div>

        <!-- GENERIC FILE VIEWER -->
        <div v-else class="w-full aspect-video bg-gradient-to-br from-gray-900 to-black rounded-3xl overflow-hidden border border-white/10 flex flex-col items-center justify-center p-12 text-center">
            <div class="w-24 h-24 bg-white/5 rounded-full flex items-center justify-center mb-6 border border-white/10">
                <FileIcon class="w-12 h-12 text-white/20" />
            </div>
            <h3 class="text-3xl font-black text-white mb-2">{{ file.title }}</h3>
            <p class="text-white/40 mb-8 font-mono text-xs uppercase tracking-widest leading-loose">{{ file.file_type || 'Formato Desconocido' }}</p>
            <a :href="streamUrl" download class="px-8 py-4 bg-gradient-to-r from-pink-600 to-purple-700 hover:from-pink-500 hover:to-purple-600 text-white font-black rounded-2xl shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-3 tracking-widest text-xs">
                <Download class="w-5 h-5" />
                DESCARGAR ARCHIVO
            </a>
        </div>

    </div>
</template>

<style scoped>
.universal-viewer {
    animation: viewer-fade-in 0.5s ease-out;
}

@keyframes viewer-fade-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

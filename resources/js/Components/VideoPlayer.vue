<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Play, Pause, Volume2, VolumeX, Maximize, Settings } from 'lucide-vue-next';

const props = defineProps<{
    src: string;
    title?: string;
    quality?: string;
}>();

const videoRef = ref<HTMLVideoElement | null>(null);
const isPlaying = ref(false);
const isMuted = ref(false);
const progress = ref(0);
const currentTime = ref(0);
const duration = ref(0);
const volume = ref(1);
const videoError = ref(false);
const skipMessage = ref('');
let skipTimeout: any = null;
let controlsTimeout: any = null;

const isScrubbing = ref(false);
const progressBarRef = ref<HTMLElement | null>(null);

const formatTime = (time: number) => {
    if (isNaN(time) || !isFinite(time)) return '00:00';
    const min = Math.floor(time / 60);
    const sec = Math.floor(time % 60);
    return `${min.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
};

const showSkipFeedback = (msg: string) => {
    skipMessage.value = msg;
    clearTimeout(skipTimeout);
    skipTimeout = setTimeout(() => skipMessage.value = '', 600);
};

const handleVideoError = () => {
    videoError.value = true;
    isPlaying.value = false;
};

const togglePlay = () => {
    if (!videoRef.value) return;
    if (isPlaying.value) {
        videoRef.value.pause();
    } else {
        videoRef.value.play();
    }
    isPlaying.value = !isPlaying.value;
};

const toggleMute = () => {
    if (!videoRef.value) return;
    isMuted.value = !isMuted.value;
    videoRef.value.muted = isMuted.value;
};

const handleProgress = () => {
    if (!videoRef.value || isScrubbing.value) return; // Don't snap back while dragging
    currentTime.value = videoRef.value.currentTime;
    duration.value = videoRef.value.duration || 0;
    progress.value = (currentTime.value / duration.value) * 100;
};

const handleScrub = (e: MouseEvent) => {
    if (!videoRef.value || !progressBarRef.value) return;
    const vidDuration = videoRef.value.duration || 0;
    if (!isFinite(vidDuration) || vidDuration === 0) return;

    const rect = progressBarRef.value.getBoundingClientRect();
    const pos = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
    videoRef.value.currentTime = pos * vidDuration;
    
    currentTime.value = videoRef.value.currentTime;
    progress.value = pos * 100;
};

const startScrubbing = (e: MouseEvent) => {
    isScrubbing.value = true;
    handleScrub(e);
    window.addEventListener('mousemove', handleScrub);
    window.addEventListener('mouseup', stopScrubbing);
};

const stopScrubbing = () => {
    isScrubbing.value = false;
    window.removeEventListener('mousemove', handleScrub);
    window.removeEventListener('mouseup', stopScrubbing);
};

const toggleFullscreen = () => {
    if (!videoRef.value) return;
    if (videoRef.value.requestFullscreen) {
        videoRef.value.requestFullscreen();
    }
};

const handleMouseMove = () => {
    showControls.value = true;
    clearTimeout(controlsTimeout);
    controlsTimeout = setTimeout(() => {
        if (isPlaying.value) showControls.value = false;
    }, 3000);
};

const handleKeyDown = (e: KeyboardEvent) => {
    if (!videoRef.value) return;
    
    // Prevent scrolling when using space
    if (e.code === 'Space') {
        e.preventDefault();
        togglePlay();
    }
    
    if (e.code === 'ArrowUp') {
        e.preventDefault();
        volume.value = Math.min(1, volume.value + 0.1);
        videoRef.value.volume = volume.value;
        if (isMuted.value && volume.value > 0) isMuted.value = false;
        showControls.value = true;
    }
    
    if (e.code === 'ArrowDown') {
        e.preventDefault();
        volume.value = Math.max(0, volume.value - 0.1);
        videoRef.value.volume = volume.value;
        showControls.value = true;
    }

    if (e.code === 'KeyM') {
        toggleMute();
        showControls.value = true;
    }

    if (e.code === 'KeyL' || e.code === 'ArrowRight') {
        e.preventDefault();
        videoRef.value.currentTime += 10;
        showSkipFeedback('⏭ +10s');
        showControls.value = true;
    }
    
    if (e.code === 'KeyJ' || e.code === 'ArrowLeft') {
        e.preventDefault();
        videoRef.value.currentTime -= 10;
        showSkipFeedback('⏮ -10s');
        showControls.value = true;
    }

    if (e.code === 'KeyF') {
        e.preventDefault();
        toggleFullscreen();
    }
};

onMounted(() => {
    if (videoRef.value) {
        videoRef.value.addEventListener('loadedmetadata', () => {
            if (videoRef.value) duration.value = videoRef.value.duration || 0;
        });
        videoRef.value.addEventListener('timeupdate', handleProgress);
        videoRef.value.addEventListener('ended', () => isPlaying.value = false);
        window.addEventListener('keydown', handleKeyDown);
    }
});

onUnmounted(() => {
    if (videoRef.value) {
        videoRef.value.removeEventListener('timeupdate', handleProgress);
    }
    window.removeEventListener('keydown', handleKeyDown);
    window.removeEventListener('mousemove', handleScrub);
    window.removeEventListener('mouseup', stopScrubbing);
    clearTimeout(controlsTimeout);
});
</script>

<template>
    <div 
        class="relative group bg-black rounded-3xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.5)] aspect-video border border-white/10"
        @mousemove="handleMouseMove"
        @mouseleave="showControls = false"
    >
        <video 
            ref="videoRef"
            :src="src"
            class="w-full h-full object-contain cursor-pointer"
            @click="togglePlay"
            @dblclick="toggleFullscreen"
            @error="handleVideoError"
            @loadeddata="videoError = false"
        ></video>

        <!-- Error Overlay -->
        <div v-if="videoError" class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-gray-900/90 backdrop-blur-sm p-6 text-center">
            <div class="p-4 bg-red-500/20 rounded-full mb-4">
                <Music class="w-12 h-12 text-red-500 animate-pulse" />
            </div>
            <h4 class="text-xl font-black text-white mb-2 uppercase tracking-tighter">Video no disponible</h4>
            <p class="text-white/40 text-sm max-w-xs">El archivo de video no pudo cargarse. Esto puede deberse a un error durante la subida o a que el archivo ya no existe en el servidor.</p>
            <button @click="videoError = false; videoRef?.load()" class="mt-6 px-6 py-2 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-xl transition-all border border-white/10">REINTENTAR CARGA</button>
        </div>

        <!-- Overlay Controls -->
        <Transition name="fade">
            <div v-show="showControls || !isPlaying" class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-black/20 flex flex-col justify-end p-6 transition-opacity duration-300 pointer-events-none">
                
                <!-- Title & Quality -->
                <div v-if="title" class="absolute top-6 left-6 flex items-center gap-3 pointer-events-auto">
                    <span class="text-white font-bold drop-shadow-lg text-lg">{{ title }}</span>
                    <span class="px-1.5 py-0.5 bg-white/10 backdrop-blur-md rounded text-[10px] font-bold text-white border border-white/20 whitespace-nowrap">{{ quality || '1080p HD' }}</span>
                </div>

                <!-- Progress Bar -->
                <div 
                    ref="progressBarRef"
                    class="w-full h-1.5 bg-white/10 rounded-full mb-6 cursor-pointer relative group/progress transition-all hover:h-2 pointer-events-auto"
                    @mousedown.prevent="startScrubbing"
                >
                    <div 
                        class="absolute top-0 left-0 h-full bg-gradient-to-r from-pink-500 to-purple-600 rounded-full shadow-[0_0_15px_rgba(236,72,153,0.5)]"
                        :style="{ width: progress + '%' }"
                    ></div>
                    <!-- Hover seeker -->
                    <div class="absolute -top-10 left-0 opacity-0 group-hover/progress:opacity-100 transition-opacity bg-white/90 text-black text-[10px] font-bold px-2 py-1 rounded-lg pointer-events-none">
                        Previsualizar
                    </div>
                </div>

                <!-- Bottom Controls -->
                <div class="flex items-center justify-between text-white pointer-events-auto">
                    <div class="flex items-center gap-6">
                        <button @click="togglePlay" class="hover:scale-125 transition-transform active:scale-95 text-pink-500">
                            <Play v-if="!isPlaying" class="w-8 h-8 fill-current" />
                            <Pause v-else class="w-8 h-8 fill-current" />
                        </button>
                        
                        <div class="flex items-center gap-3 group/volume">
                             <button @click="toggleMute" class="hover:text-pink-400 transition-colors">
                                <VolumeX v-if="isMuted || volume === 0" class="w-6 h-6 text-red-400" />
                                <Volume2 v-else class="w-6 h-6" />
                            </button>
                            <input 
                                type="range" 
                                v-model="volume" 
                                min="0" max="1" step="0.1"
                                @input="videoRef!.volume = volume"
                                class="w-0 group-hover/volume:w-24 transition-all origin-left accent-pink-500 h-1.5 bg-white/20 rounded-full cursor-pointer"
                            />
                        </div>

                        <!-- Tiempo de reproducción (Playback Status) -->
                        <div class="text-white text-xs font-mono font-medium tracking-wider opacity-80 select-none">
                            {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
                        </div>

                    </div>

                    <div class="flex items-center gap-6">
                        <button class="hover:rotate-90 transition-all duration-300 text-white/60 hover:text-white">
                            <Settings class="w-6 h-6" />
                        </button>
                        <button @click="toggleFullscreen" class="hover:scale-125 transition-transform text-white/60 hover:text-white">
                            <Maximize class="w-6 h-6" />
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Skip Feedback Indicator -->
        <Transition name="fade">
            <div v-if="skipMessage" class="absolute inset-0 m-auto w-24 h-24 bg-black/50 backdrop-blur-md text-white rounded-full flex items-center justify-center font-bold text-lg shadow-2xl pointer-events-none">
                {{ skipMessage }}
            </div>
        </Transition>

        <!-- Centered Play/Pause Big Icon -->

        <Transition name="scale">
            <button 
                v-if="!isPlaying && showControls" 
                @click="togglePlay"
                class="absolute inset-0 m-auto w-24 h-24 bg-gradient-to-br from-pink-600/90 to-purple-700/90 text-white rounded-full flex items-center justify-center shadow-2xl hover:scale-110 active:scale-90 transition-all border border-white/20"
            >
                <Play class="w-12 h-12 ml-1 fill-current shadow-black" />
            </button>
        </Transition>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

.scale-enter-active, .scale-leave-active {
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.2s ease;
}
.scale-enter-from, .scale-leave-to {
    transform: scale(0.5);
    opacity: 0;
}

input[type="range"] {
    -webkit-appearance: none;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 5px;
}

input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    height: 12px;
    width: 12px;
    border-radius: 50%;
    background: #3b82f6;
    cursor: pointer;
}
</style>

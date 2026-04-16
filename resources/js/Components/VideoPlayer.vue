<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Play, Pause, Volume2, VolumeX, Maximize, Settings } from 'lucide-vue-next';

const props = defineProps<{
    src: string;
    title?: string;
}>();

const videoRef = ref<HTMLVideoElement | null>(null);
const isPlaying = ref(false);
const isMuted = ref(false);
const progress = ref(0);
const volume = ref(1);
const showControls = ref(true);
let controlsTimeout: any = null;

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
    if (!videoRef.value) return;
    progress.value = (videoRef.value.currentTime / videoRef.value.duration) * 100;
};

const seek = (e: MouseEvent) => {
    if (!videoRef.value) return;
    const rect = (e.target as HTMLElement).getBoundingClientRect();
    const pos = (e.clientX - rect.left) / rect.width;
    videoRef.value.currentTime = pos * videoRef.value.duration;
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
    
    if (e.code === 'ArrowRight') {
        videoRef.value.currentTime += 5;
    }
    
    if (e.code === 'ArrowLeft') {
        videoRef.value.currentTime -= 5;
    }

    if (e.code === 'KeyF') {
        toggleFullscreen();
    }
};

onMounted(() => {
    if (videoRef.value) {
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
            class="w-full h-full object-contain"
            @click="togglePlay"
        ></video>

        <!-- Overlay Controls -->
        <Transition name="fade">
            <div v-show="showControls || !isPlaying" class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-black/20 flex flex-col justify-end p-6 transition-opacity duration-300">
                
                <!-- Title & Quality -->
                <div v-if="title" class="absolute top-6 left-6 flex items-center gap-3">
                    <span class="text-white font-bold drop-shadow-lg text-lg">{{ title }}</span>
                    <span class="px-1.5 py-0.5 bg-white/10 backdrop-blur-md rounded text-[10px] font-bold text-white border border-white/20 whitespace-nowrap">4K ULTRA HD</span>
                </div>

                <!-- Progress Bar -->
                <div 
                    class="w-full h-1.5 bg-white/10 rounded-full mb-6 cursor-pointer relative group/progress transition-all hover:h-2"
                    @click="seek"
                >
                    <div 
                        class="absolute top-0 left-0 h-full bg-gradient-to-r from-pink-500 to-purple-600 rounded-full shadow-[0_0_15px_rgba(236,72,153,0.5)]"
                        :style="{ width: progress + '%' }"
                    ></div>
                    <!-- Hover seeker -->
                    <div class="absolute -top-10 left-0 opacity-0 group-hover/progress:opacity-100 transition-opacity bg-white/90 text-black text-[10px] font-bold px-2 py-1 rounded-lg">
                        Previsualizar
                    </div>
                </div>

                <!-- Bottom Controls -->
                <div class="flex items-center justify-between text-white">
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

                        <div class="flex items-center gap-2 text-[11px] font-mono text-white/60">
                            <span>HD</span>
                            <div class="w-1 h-1 bg-white/20 rounded-full"></div>
                            <span>{{ title }}</span>
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

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

onMounted(() => {
    if (videoRef.value) {
        videoRef.value.addEventListener('timeupdate', handleProgress);
        videoRef.value.addEventListener('ended', () => isPlaying.value = false);
    }
});

onUnmounted(() => {
    if (videoRef.value) {
        videoRef.value.removeEventListener('timeupdate', handleProgress);
    }
    clearTimeout(controlsTimeout);
});
</script>

<template>
    <div 
        class="relative group bg-black rounded-xl overflow-hidden shadow-2xl aspect-video border border-white/10"
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
            <div v-show="showControls || !isPlaying" class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-black/20 flex flex-col justify-end p-4 transition-opacity duration-300">
                
                <!-- Title -->
                <div v-if="title" class="absolute top-4 left-4 text-white font-medium drop-shadow-md">
                    {{ title }}
                </div>

                <!-- Progress Bar -->
                <div 
                    class="w-full h-1.5 bg-white/20 rounded-full mb-4 cursor-pointer relative overflow-hidden group/progress"
                    @click="seek"
                >
                    <div 
                        class="absolute top-0 left-0 h-full bg-blue-500 transition-all"
                        :style="{ width: progress + '%' }"
                    ></div>
                </div>

                <!-- Bottom Controls -->
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center gap-4">
                        <button @click="togglePlay" class="hover:scale-110 transition-transform">
                            <Play v-if="!isPlaying" class="w-6 h-6 fill-current" />
                            <Pause v-else class="w-6 h-6 fill-current" />
                        </button>
                        
                        <div class="flex items-center gap-2 group/volume">
                             <button @click="toggleMute">
                                <VolumeX v-if="isMuted || volume === 0" class="w-5 h-5" />
                                <Volume2 v-else class="w-5 h-5" />
                            </button>
                            <input 
                                type="range" 
                                v-model="volume" 
                                min="0" max="1" step="0.1"
                                @input="videoRef!.volume = volume"
                                class="w-0 group-hover/volume:w-20 transition-all origin-left accent-blue-500 h-1"
                            />
                        </div>

                        <span class="text-xs opacity-70 font-mono">
                            {{ title || 'Streaming Content' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-4">
                        <button class="hover:rotate-45 transition-transform">
                            <Settings class="w-5 h-5" />
                        </button>
                        <button @click="toggleFullscreen" class="hover:scale-110 transition-transform">
                            <Maximize class="w-5 h-5" />
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
                class="absolute inset-0 m-auto w-20 h-20 bg-blue-600/90 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-blue-500 transition-all scale-100 hover:scale-110"
            >
                <Play class="w-10 h-10 ml-1 fill-current" />
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

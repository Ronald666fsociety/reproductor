<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue';
import Plyr from 'plyr';
import 'plyr/dist/plyr.css';
import { Music } from 'lucide-vue-next';

const props = defineProps<{
    src: string;
    title?: string;
}>();

const videoElement = ref<HTMLVideoElement | null>(null);
const videoError = ref(false);
let player: Plyr | null = null;

onMounted(() => {
    if (videoElement.value) {
        player = new Plyr(videoElement.value, {
            controls: [
                'play-large',
                'play',
                'progress',
                'current-time',
                'duration',
                'mute',
                'volume',
                'captions',
                'settings',
                'pip',
                'fullscreen',
            ],
            settings: ['quality', 'speed', 'loop'],
            speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 2] },
            keyboard: { focused: true, global: true },
            tooltips: { controls: true, seek: true },
            i18n: {
                restart: 'Reiniciar',
                play: 'Reproducir',
                pause: 'Pausar',
                fastForward: 'Adelantar 10s',
                seek: 'Buscar',
                seekLabel: '{currentTime} de {duration}',
                played: 'Reproducido',
                buffered: 'Almacenado en buffer',
                currentTime: 'Tiempo actual',
                duration: 'Duración',
                volume: 'Volumen',
                mute: 'Silenciar',
                unmute: 'Activar sonido',
                download: 'Descargar',
                enterFullscreen: 'Pantalla completa',
                exitFullscreen: 'Salir de pantalla completa',
                frameTitle: 'Reproductor para {title}',
                settings: 'Configuración',
                speed: 'Velocidad',
                normal: 'Normal',
                quality: 'Calidad',
                loop: 'Bucle',
            }
        });
        
        // Error handling
        videoElement.value.addEventListener('error', () => {
            videoError.value = true;
        });
        videoElement.value.addEventListener('loadeddata', () => {
            videoError.value = false;
        });
    }
});

onUnmounted(() => {
    if (player) {
        player.destroy();
    }
});

// React to source changes if parent component swaps the video
watch(() => props.src, () => {
    if (player) {
        player.source = {
            type: 'video',
            sources: [
                {
                    src: props.src,
                    type: 'video/mp4',
                },
            ],
        };
        videoError.value = false;
        
        // Auto-play the new video immediately to avoid the "double click to play" issue
        // We use a slight delay to ensure Plyr has completely loaded the new source
        setTimeout(() => {
            const playPromise = player?.play();
            if (playPromise !== undefined) {
                playPromise.catch((e: any) => console.log('Autoplay prevented', e));
            }
        }, 150);
    }
});
</script>

<template>
    <div class="plyr-container relative rounded-3xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.5)] border border-white/10 bg-black w-full aspect-video max-h-[70vh] flex items-center justify-center">
        
        <!-- Error Overlay -->
        <div v-if="videoError" class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-gray-900/90 backdrop-blur-sm p-6 text-center">
            <div class="p-4 bg-red-500/20 rounded-full mb-4">
                <Music class="w-12 h-12 text-red-500 animate-pulse" />
            </div>
            <h4 class="text-xl font-black text-white mb-2 uppercase tracking-tighter">Video no disponible</h4>
            <p class="text-white/40 text-sm max-w-xs">El archivo de video no pudo cargarse o su formato es incompatible.</p>
            <button @click="videoError = false; player?.play()" class="mt-6 px-6 py-2 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-xl transition-all border border-white/10">REINTENTAR CARGA</button>
        </div>

        <!-- Plyr Video Element -->
        <video 
            ref="videoElement" 
            playsinline 
            controls 
            crossorigin="anonymous"
            class="plyr-video"
        >
            <source :src="src" type="video/mp4" />
        </video>
        
        <!-- Custom Title Overlay -->
        <div v-if="title && !videoError" class="absolute top-4 left-4 z-10 pointer-events-none fade-out-on-play">
            <span class="px-3 py-1 bg-black/60 backdrop-blur-md rounded-lg text-white font-bold drop-shadow-lg text-sm border border-white/10">{{ title }}</span>
        </div>
    </div>
</template>

<style>
/* Plyr overrides for our premium pink theme */
.plyr-container {
    --plyr-color-main: #ec4899; /* Pink-500 */
    --plyr-video-background: #000;
    --plyr-menu-background: rgba(17, 24, 39, 0.95);
    --plyr-menu-border-color: rgba(255, 255, 255, 0.1);
    --plyr-menu-color: #fff;
    --plyr-tooltip-background: rgba(17, 24, 39, 0.9);
    --plyr-font-family: inherit;
    --plyr-video-control-background-hover: rgba(236, 72, 153, 1);
}

.plyr {
    width: 100%;
    height: 100%;
}

.plyr__video-wrapper {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #000;
}

video {
    max-height: 100%;
    object-fit: contain;
}

.plyr--video .plyr__controls {
    background: linear-gradient(rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.9));
    padding: 30px 20px 20px !important;
}

.plyr__control--overlaid {
    background: rgba(236, 72, 153, 0.8) !important;
}

.plyr__control--overlaid:hover {
    background: #ec4899 !important;
    transform: scale(1.1);
}

.plyr__menu__container {
    border-radius: 1rem;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.fade-out-on-play {
    transition: opacity 0.3s ease;
}
.plyr--playing .fade-out-on-play {
    opacity: 0;
}
</style>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import VideoPlayer from '@/Components/VideoPlayer.vue';
import { FileIcon, Download, Music, ShieldCheck, Clock } from 'lucide-vue-next';

const props = defineProps<{
    video: {
        id: number;
        title: string;
        path: string;
        file_type: string | null;
    };
    category_name: string;
}>();

const formatSize = (bytes: number | null) => {
    if (!bytes) return 'N/A';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <Head :title="'Compartido: ' + video.title" />

    <div class="min-h-screen bg-[#050505] text-white selection:bg-pink-500/30">
        <!-- Header -->
        <nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
            <div class="max-w-6xl mx-auto px-6 h-20 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-pink-600 to-purple-700 rounded-xl flex items-center justify-center shadow-lg shadow-pink-900/20">
                        <ShieldCheck class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h1 class="text-lg font-black tracking-tighter leading-none">MEDIA VAULT</h1>
                        <p class="text-[10px] font-bold text-pink-500 uppercase tracking-widest mt-0.5">Enlace Seguro</p>
                    </div>
                </div>
                
                <div class="hidden sm:flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 rounded-full">
                    <Clock class="w-3.5 h-3.5 text-pink-500" />
                    <span class="text-[10px] font-black uppercase tracking-widest">El enlace caduca pronto</span>
                </div>
            </div>
        </nav>

        <main class="max-w-6xl mx-auto px-6 py-12 lg:py-20">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                
                <!-- Left: Info & Actions -->
                <div class="order-2 lg:order-1 animate-in fade-in slide-in-from-left duration-1000">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="px-3 py-1 bg-pink-500/10 border border-pink-500/20 text-pink-500 text-[10px] font-black uppercase tracking-[0.2em] rounded-lg">Archivo Compartido</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-white/10"></span>
                        <span class="text-white/40 text-[10px] font-black uppercase tracking-[0.2em]">{{ category_name }}</span>
                    </div>

                    <h2 class="text-5xl lg:text-7xl font-black tracking-tighter leading-[0.9] mb-8 text-white drop-shadow-2xl">
                        {{ video.title }}
                    </h2>

                    <p class="text-white/40 text-lg mb-12 font-medium max-w-lg leading-relaxed">
                        Este archivo ha sido compartido contigo de forma segura a través de Media Vault. Puedes previsualizarlo directamente o descargarlo a continuación.
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <a 
                            :href="'/storage/' + video.path" 
                            download 
                            target="_blank"
                            class="flex-1 min-w-[200px] flex items-center justify-center gap-3 px-8 py-5 bg-gradient-to-r from-pink-600 to-purple-700 hover:from-pink-500 hover:to-purple-600 text-white font-black rounded-2xl shadow-xl shadow-pink-900/40 transition-all hover:scale-[1.02] active:scale-95"
                        >
                            <Download class="w-6 h-6" />
                            DESCARGAR ARCHIVO
                        </a>
                        
                        <div class="px-6 py-5 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-center text-white/40 text-sm font-bold uppercase tracking-widest">
                            {{ video.file_type?.split('/')[1] || 'FILE' }}
                        </div>
                    </div>
                </div>

                <!-- Right: Visualizer -->
                <div class="order-1 lg:order-2 animate-in fade-in zoom-in duration-1000">
                    <div class="glass-premium rounded-[3rem] p-4 lg:p-8 border border-white/10 shadow-3xl relative">
                        <!-- Decorative background glow -->
                        <div class="absolute -inset-1 bg-gradient-to-r from-pink-600 to-purple-600 rounded-[3.5rem] opacity-20 blur-2xl -z-10"></div>

                        <!-- Reproductor de Video -->
                        <VideoPlayer 
                            v-if="video.file_type && video.file_type.startsWith('video/')"
                            :src="'/storage/' + video.path"
                            :title="video.title"
                        />

                        <!-- Visor de Imágenes -->
                        <div v-else-if="video.file_type && video.file_type.startsWith('image/')" class="w-full bg-black rounded-3xl overflow-hidden shadow-2xl border border-white/10 flex items-center justify-center min-h-[300px]">
                            <img :src="'/storage/' + video.path" class="w-full h-full object-contain max-h-[60vh]" />
                        </div>

                        <!-- Visor de Audio -->
                        <div v-else-if="video.file_type && video.file_type.startsWith('audio/')" class="w-full bg-gradient-to-br from-indigo-900 to-black rounded-3xl overflow-hidden flex flex-col items-center justify-center min-h-[300px] p-8 text-center">
                            <div class="w-24 h-24 bg-indigo-500/20 rounded-full flex items-center justify-center mb-6 relative">
                                <div class="absolute inset-0 border-2 border-indigo-500 rounded-full animate-[spin_4s_linear_infinite] border-t-transparent"></div>
                                <Music class="w-10 h-10 text-indigo-400" />
                            </div>
                            <audio controls :src="'/storage/' + video.path" class="w-full max-w-md mt-4"></audio>
                        </div>

                        <!-- Visor de PDF -->
                        <div v-else-if="video.file_type === 'application/pdf'" class="w-full h-[50vh] bg-gray-900 rounded-3xl overflow-hidden shadow-2xl border border-white/10">
                            <iframe :src="'/storage/' + video.path + '#toolbar=0'" class="w-full h-full border-0"></iframe>
                        </div>

                        <!-- Genérico -->
                        <div v-else class="w-full aspect-video bg-gradient-to-br from-gray-900 to-black rounded-3xl flex flex-col items-center justify-center p-8 text-center">
                            <FileIcon class="w-16 h-16 text-white/20 mb-4" />
                            <p class="text-white/40 font-bold uppercase tracking-widest text-xs">Vista previa no disponible</p>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <!-- Footer Footer -->
        <footer class="mt-20 border-t border-white/5 py-12 text-center">
            <p class="text-white/20 text-xs font-bold uppercase tracking-widest">Desarrollado con Media Vault Server &copy; 2026</p>
        </footer>
    </div>
</template>

<style scoped>
.glass-premium {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.01));
    backdrop-filter: blur(20px);
}
</style>

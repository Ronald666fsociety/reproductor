<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import VideoPlayer from '@/Components/VideoPlayer.vue';
import { Play, Film, Upload, Trash2, ArrowLeft, Grid, List as ListIcon } from 'lucide-vue-next';

interface Video {
    id: number;
    title: string;
    path: string;
    thumbnail_path: string | null;
    order: number;
}

interface Category {
    id: number;
    name: string;
    videos: Video[];
}

const props = defineProps<{
    category: Category;
}>();

const user = (usePage().props.auth as any).user;
const selectedVideo = ref<Video | null>(props.category.videos.length > 0 ? props.category.videos[0] : null);
const showUploadModal = ref(false);
const isUploaded = ref(false);
const viewMode = ref<'grid' | 'list'>('grid');

const uploadForm = useForm({
    title: '',
    category_id: props.category.id,
    video: null as File | null,
});

const handleFileUpload = (e: any) => {
    uploadForm.video = e.target.files[0];
    if (!uploadForm.title && uploadForm.video) {
        uploadForm.title = uploadForm.video.name.replace(/\.[^/.]+$/, "");
    }
};

const submitUpload = () => {
    uploadForm.post(route('video.upload'), {
        forceFormData: true,
        onSuccess: () => {
            isUploaded.value = true;
            setTimeout(() => {
                showUploadModal.value = false;
                isUploaded.value = false;
                uploadForm.reset();
            }, 3000);
        },
    });
};

const deleteVideo = (id: number) => {
    if (confirm('¿Eliminar este video permanentemente?')) {
        useForm({}).delete(route('video.destroy', id));
    }
};

const selectVideo = (video: Video) => {
    selectedVideo.value = video;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>

<template>
    <Head :title="category.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <Link :href="route('categories.index')" class="p-2 bg-white/5 hover:bg-white/10 rounded-xl transition-colors">
                        <ArrowLeft class="w-5 h-5 text-white" />
                    </Link>
                    <h2 class="text-2xl font-bold text-white">{{ category.name }}</h2>
                </div>
                
                <div class="flex gap-2">
                    <button 
                        v-if="user.is_admin"
                        @click="showUploadModal = true"
                        class="flex items-center gap-2 bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg transition-all"
                    >
                        <Upload class="w-4 h-4" />
                        Subir Video
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Player Area -->
                    <div class="lg:col-span-2 space-y-6">
                        <div v-if="selectedVideo" class="glass-premium rounded-[3rem] overflow-hidden shadow-2xl p-6 border border-white/10 animate-in slide-in-from-bottom duration-700">
                            <VideoPlayer 
                                :key="selectedVideo.id"
                                :src="'/storage/' + selectedVideo.path"
                                :title="selectedVideo.title"
                            />
                            <div class="mt-8 px-2 flex justify-between items-end">
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="px-2 py-1 bg-pink-500/20 text-pink-400 text-[10px] font-black rounded-lg uppercase tracking-widest border border-pink-500/20">En Reproducción</span>
                                        <span class="w-1 h-1 bg-white/20 rounded-full"></span>
                                        <span class="text-white/40 text-[10px] font-bold uppercase tracking-widest">{{ category.name }}</span>
                                    </div>
                                    <h3 class="text-3xl font-black text-white tracking-tighter leading-none">{{ selectedVideo.title }}</h3>
                                </div>
                                <div v-if="user.is_admin" class="flex gap-3">
                                    <button @click="deleteVideo(selectedVideo.id)" class="p-4 bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white rounded-2xl transition-all duration-300 shadow-lg hover:shadow-red-500/20 active:scale-95">
                                        <Trash2 class="w-6 h-6" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="aspect-video glass-premium rounded-[3rem] border-2 border-dashed border-white/5 flex flex-col items-center justify-center p-12 text-center animate-in fade-in zoom-in duration-500">
                            <div class="p-6 bg-white/5 rounded-full mb-6 border border-white/10">
                                <Film class="w-16 h-16 text-pink-500/40" />
                            </div>
                            <h4 class="text-2xl font-black text-white mb-2 tracking-tighter uppercase">Selecciona un Video</h4>
                            <p class="text-white/30 text-sm max-w-xs font-medium">Explora la galería a la derecha para comenzar tu reproducción privada con la mejor calidad.</p>
                        </div>

                        <!-- Description or Info -->
                        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl p-8">
                            <h4 class="text-white font-bold mb-4">Detalles de la Serie</h4>
                            <p class="text-white/60 leading-relaxed text-sm">
                                Estás en la sección exclusiva de {{ category.name }}. Aquí encontrarás todo el material enumerado y previsualizado para una mejor experiencia de visualización privada.
                            </p>
                        </div>
                    </div>

                    <!-- Sidebar / List -->
                    <div class="space-y-6">
                        <div class="bg-gray-950/50 backdrop-blur-xl border border-white/10 rounded-3xl p-6 h-[85vh] sticky top-8 flex flex-col">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                    <PlayCircle class="w-5 h-5 text-pink-500" />
                                    Galería ({{ category.videos.length }})
                                </h3>
                                <div class="flex gap-1 bg-white/5 p-1 rounded-lg">
                                    <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-pink-600 text-white' : 'text-white/40'" class="p-1 px-2 rounded-md transition-colors"><Grid class="w-4 h-4" /></button>
                                    <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-pink-600 text-white' : 'text-white/40'" class="p-1 px-2 rounded-md transition-colors"><ListIcon class="w-4 h-4" /></button>
                                </div>
                            </div>

                            <div class="overflow-y-auto flex-1 custom-scrollbar space-y-3 pr-2">
                                <div 
                                    v-for="(video, index) in category.videos" 
                                    :key="video.id"
                                    @click="selectVideo(video)"
                                    class="group relative flex gap-4 p-4 rounded-3xl border cursor-pointer transition-all duration-500 overflow-hidden"
                                    :class="selectedVideo?.id === video.id ? 'bg-pink-500/10 border-pink-500/50 shadow-[0_10px_30px_rgba(236,72,153,0.1)]' : 'bg-white/5 border-white/5 hover:bg-white/10 hover:border-white/10'"
                                >
                                    <div class="relative w-28 h-20 flex-shrink-0 bg-black rounded-[1.25rem] overflow-hidden border border-white/10 shadow-lg group-hover:scale-105 transition-transform duration-500">
                                        <img 
                                            v-if="video.thumbnail_path" 
                                            :src="'/storage/' + video.thumbnail_path" 
                                            class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity"
                                            @error="(e) => (e.target as HTMLImageElement).src = '/images/thumbnail-placeholder.png'"
                                        />
                                        <div v-else class="flex flex-col items-center justify-center w-full h-full bg-gradient-to-br from-gray-800 to-gray-950 text-white/20">
                                            <Music class="w-6 h-6 mb-1 opacity-50" />
                                            <span class="text-[8px] font-black uppercase tracking-widest">Premium</span>
                                        </div>
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                        <div class="absolute bottom-2 right-2 px-1.5 py-0.5 bg-pink-600/80 backdrop-blur-md text-[8px] text-white font-black rounded-md">
                                            #{{ index + 1 }}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0 py-2">
                                        <p class="text-sm font-black text-white truncate group-hover:text-pink-400 transition-colors tracking-tight">{{ video.title }}</p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <span class="w-1 h-1 bg-pink-500 rounded-full"></span>
                                            <p class="text-[9px] text-white/40 font-bold uppercase tracking-widest">Disponible ahora</p>
                                        </div>
                                    </div>
                                    <div v-if="selectedVideo?.id === video.id" class="flex items-center">
                                        <div class="w-2 h-2 bg-pink-500 rounded-full shadow-[0_0_15px_#ec4899] animate-pulse"></div>
                                    </div>
                                    
                                    <!-- Hover Glow -->
                                    <div class="absolute -right-4 -bottom-4 w-12 h-12 bg-pink-600/10 blur-xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Upload Modal (Admin Only) -->
        <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="bg-gray-900 border border-white/20 w-full max-w-md rounded-3xl p-8 shadow-2xl">
                <h3 class="text-2xl font-bold text-white mb-6">Subir Nuevo Video</h3>
                <form @submit.prevent="submitUpload" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-white/60 mb-2">Título del Video</label>
                        <input v-model="uploadForm.title" type="text" class="w-full bg-white/5 border-white/10 rounded-xl text-white focus:ring-pink-500" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white/60 mb-2">Archivo de Video</label>
                        <input type="file" @change="handleFileUpload" accept="video/*" class="w-full text-white text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-white/10 file:text-white hover:file:bg-white/20" />
                        
                        <!-- Progress Bar (The requested "como se esta cargando") -->
                        <div v-if="uploadForm.progress" class="mt-4">
                            <div class="flex justify-between text-[10px] text-pink-400 font-bold mb-1 uppercase tracking-wider">
                                <span>Subiendo Contenido...</span>
                                <span>{{ uploadForm.progress.percentage }}%</span>
                            </div>
                            <div class="w-full bg-white/5 rounded-full h-2 overflow-hidden border border-white/10">
                                <div 
                                    class="bg-gradient-to-r from-pink-500 to-purple-600 h-full transition-all duration-300 shadow-[0_0_10px_rgba(236,72,153,0.5)]" 
                                    :style="{ width: uploadForm.progress.percentage + '%' }"
                                ></div>
                            </div>
                        </div>

                        <!-- Processing State (FFmpeg) -->
                        <div v-if="uploadForm.processing && !uploadForm.progress" class="mt-4 flex items-center gap-3 text-yellow-400 animate-pulse">
                            <div class="w-4 h-4 border-2 border-yellow-400 border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-xs font-bold uppercase tracking-widest">Procesando Miniatura con FFmpeg...</span>
                        </div>

                        <!-- Success Feedback (The requested "VIDEO CARGADO") -->
                        <div v-if="isUploaded" class="mt-6 p-4 bg-green-500/10 border border-green-500/50 rounded-2xl text-green-400 text-center font-black text-xl tracking-tighter animate-in zoom-in duration-300">
                             ✨ ¡VIDEO CARGADO CON ÉXITO!
                        </div>
                    </div>
                    <div v-if="!isUploaded" class="flex gap-4 pt-4">
                        <button type="button" @click="showUploadModal = false" class="flex-1 py-3 text-white/60 font-bold hover:text-white transition-colors">Cancelar</button>
                        <button 
                            type="submit" 
                            :disabled="uploadForm.processing || !uploadForm.video" 
                            class="flex-1 py-3 bg-gradient-to-r from-pink-600 to-purple-700 disabled:from-gray-800 disabled:to-gray-900 disabled:text-white/20 text-white font-bold rounded-xl shadow-lg shadow-pink-900/40 transition-all hover:scale-[1.02] active:scale-[0.98]"
                        >
                            {{ uploadForm.processing ? 'Espere...' : 'Comenzar Subida' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>

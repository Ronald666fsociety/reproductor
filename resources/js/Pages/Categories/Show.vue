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
        onSuccess: () => {
            showUploadModal.value = false;
            uploadForm.reset();
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
                        <div v-if="selectedVideo" class="bg-black/40 backdrop-blur-xl border border-white/10 rounded-3xl overflow-hidden shadow-2xl p-4">
                            <VideoPlayer 
                                :key="selectedVideo.id"
                                :src="'/storage/' + selectedVideo.path"
                                :title="selectedVideo.title"
                            />
                            <div class="mt-6 flex justify-between items-start">
                                <div>
                                    <h3 class="text-2xl font-bold text-white">{{ selectedVideo.title }}</h3>
                                    <p class="text-white/40 mt-1">Reproduciendo ahora &bull; Sección: {{ category.name }}</p>
                                </div>
                                <div v-if="user.is_admin" class="flex gap-2">
                                    <button @click="deleteVideo(selectedVideo.id)" class="p-3 bg-red-500/10 text-red-500 hover:bg-red-500 rounded-2xl transition-colors">
                                        <Trash2 class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="aspect-video bg-white/5 backdrop-blur-md rounded-3xl border border-dashed border-white/20 flex flex-col items-center justify-center">
                            <Film class="w-16 h-16 text-white/10 mb-4" />
                            <p class="text-white/30">Selecciona un video de la galería para comenzar</p>
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
                                    class="group relative flex gap-4 p-3 rounded-2xl border cursor-pointer transition-all duration-300"
                                    :class="selectedVideo?.id === video.id ? 'bg-pink-500/20 border-pink-500/50' : 'bg-white/5 border-transparent hover:bg-white/10'"
                                >
                                    <div class="relative w-24 h-16 flex-shrink-0 bg-black rounded-lg overflow-hidden border border-white/10">
                                        <img 
                                            v-if="video.thumbnail_path" 
                                            :src="'/storage/' + video.thumbnail_path" 
                                            class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-opacity"
                                        />
                                        <div v-else class="flex items-center justify-center w-full h-full text-white/20">
                                            <Film class="w-6 h-6" />
                                        </div>
                                        <div class="absolute bottom-1 right-1 px-1 bg-black/60 text-[8px] text-white rounded">
                                            #{{ index + 1 }}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0 py-1">
                                        <p class="text-sm font-bold text-white truncate">{{ video.title }}</p>
                                        <p class="text-[10px] text-white/40 mt-1 uppercase tracking-wider">Video {{ index + 1 }}</p>
                                    </div>
                                    <div v-if="selectedVideo?.id === video.id" class="flex items-center">
                                        <div class="w-1.5 h-1.5 bg-pink-500 rounded-full shadow-[0_0_10px_#ec4899]"></div>
                                    </div>
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
                        <div v-if="uploadForm.processing" class="mt-2 text-xs text-pink-400">Subiendo video... esto puede tardar unos minutos.</div>
                    </div>
                    <div class="flex gap-4 pt-4">
                        <button type="button" @click="showUploadModal = false" class="flex-1 py-3 text-white/60 font-bold">Cancelar</button>
                        <button type="submit" :disabled="uploadForm.processing" class="flex-1 py-3 bg-pink-600 border-none text-white font-bold rounded-xl shadow-lg shadow-pink-900/40">Comenzar Subida</button>
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

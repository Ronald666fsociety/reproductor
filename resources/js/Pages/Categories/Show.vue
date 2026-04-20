<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import VideoPlayer from '@/Components/VideoPlayer.vue';
import { Play, Film, Upload, Trash2, ArrowLeft, Grid, List as ListIcon, Image as ImageIcon, File as FileIcon, Download, Music, PlayCircle, Share2, Clipboard, Link as LinkIcon, CheckCircle, Info, Star, Tag as TagIcon, Filter } from 'lucide-vue-next';

interface Video {
    id: number;
    title: string;
    description: string | null;
    path: string;
    thumbnail_path: string | null;
    file_type: string | null;
    file_size: number | null;
    is_favorite: boolean;
    tags: Array<{ name: string, id: number }>;
    order: number;
    created_at: string;
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
const showFavoritesOnly = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

const uploadQueue = ref<any[]>([]);
const isUploading = ref(false);
const showShareModal = ref(false);
const generatedLink = ref('');
const isCopying = ref(false);

const filteredVideos = computed(() => {
    if (showFavoritesOnly.value) {
        return props.category.videos.filter(v => v.is_favorite);
    }
    return props.category.videos;
});

const formatSize = (bytes: number | null) => {
    if (!bytes) return 'N/A';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

const handleFiles = (files: FileList | null) => {
    if (!files) return;
    for (let i = 0; i < files.length; i++) {
        uploadQueue.value.push({
            id: Math.random().toString(36).substring(7),
            file: files[i],
            name: files[i].name.replace(/\.[^/.]+$/, ""),
            description: '',
            tags: '',
            progress: 0,
            status: 'pending' // pending, uploading, success, error
        });
    }
};

const shareVideo = async (video: Video) => {
    try {
        const response = await axios.post(route('video.share', video.id));
        generatedLink.value = response.data.link;
        showShareModal.value = true;
    } catch (error) {
        alert('Error al generar enlace de compartir.');
    }
};

const copyLink = () => {
    navigator.clipboard.writeText(generatedLink.value);
    isCopying.value = true;
    setTimeout(() => isCopying.value = false, 2000);
};

const downloadAll = () => {
    window.location.href = route('categories.download', props.category.id);
};

const handleDrop = (e: DragEvent) => {
    handleFiles(e.dataTransfer?.files || null);
};

const handleFileInput = (e: any) => {
    handleFiles(e.target.files);
    e.target.value = ''; // Reset input
};

const removeFile = (id: string) => {
    uploadQueue.value = uploadQueue.value.filter(f => f.id !== id);
};

const submitUpload = async () => {
    if (isUploading.value || uploadQueue.value.length === 0) return;
    isUploading.value = true;

    for (let i = 0; i < uploadQueue.value.length; i++) {
        const item = uploadQueue.value[i];
        if (item.status === 'success') continue;

        item.status = 'uploading';
        
        await new Promise<void>((resolve) => {
            const form = useForm({
                category_id: props.category.id,
                title: item.name,
                description: item.description,
                tags: item.tags,
                video: item.file
            });

            form.post(route('video.upload'), {
                forceFormData: true,
                preserveScroll: true,
                preserveState: true,
                onProgress: (e: any) => {
                    item.progress = Math.round((e.loaded / e.total) * 100);
                },
                onSuccess: () => {
                    item.status = 'success';
                    item.progress = 100;
                    resolve();
                },
                onError: () => {
                    item.status = 'error';
                    resolve();
                }
            });
        });
    }

    isUploading.value = false;
    
    // Check if all are success to close modal
    if (uploadQueue.value.every(i => i.status === 'success')) {
        setTimeout(() => {
            showUploadModal.value = false;
            uploadQueue.value = [];
        }, 1500);
    }
};

const deleteVideo = (id: number) => {
    if (confirm('¿Eliminar este video permanentemente?')) {
        useForm({}).delete(route('video.destroy', id), {
            preserveScroll: true,
            preserveState: false,
        });
    }
};

const toggleFavorite = (video: Video) => {
    router.post(route('video.favorite', video.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            video.is_favorite = !video.is_favorite;
        }
    });
};

const selectVideo = (video: Video) => {
    selectedVideo.value = video;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const playPreview = (e: MouseEvent) => {
    const videoElement = (e.currentTarget as HTMLElement).querySelector('video');
    if (videoElement) {
        videoElement.muted = true;
        // Salto el primer segundo para evitar posibles miniaturas negras
        videoElement.currentTime = 1;
        videoElement.play().catch(() => {});
    }
};

const stopPreview = (e: MouseEvent) => {
    const videoElement = (e.currentTarget as HTMLElement).querySelector('video');
    if (videoElement) {
        videoElement.pause();
        videoElement.currentTime = 0;
    }
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
                        @click="downloadAll"
                        class="flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg transition-all"
                    >
                        <Download class="w-4 h-4" />
                        Pack Completo (ZIP)
                    </button>
                    <button 
                        @click="showUploadModal = true"
                        class="flex items-center gap-2 bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg transition-all"
                    >
                        <Upload class="w-4 h-4" />
                        Subir Archivos
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Main Display Area -->
                    <div class="lg:col-span-2 space-y-6">
                        <div v-if="selectedVideo" class="glass-premium rounded-[3rem] overflow-hidden shadow-2xl p-6 border border-white/10 animate-in slide-in-from-bottom duration-700">
                            
                            <!-- Reproductor de Video -->
                            <VideoPlayer 
                                v-if="selectedVideo.file_type && selectedVideo.file_type.startsWith('video/')"
                                :key="selectedVideo.id"
                                :src="route('video.stream', selectedVideo.id)"
                                :title="selectedVideo.title"
                            />

                            <!-- Visor de Imágenes -->
                            <div v-else-if="selectedVideo.file_type && selectedVideo.file_type.startsWith('image/')" class="w-full bg-black rounded-3xl overflow-hidden shadow-2xl border border-white/10 flex items-center justify-center max-h-[70vh] min-h-[300px]">
                                <img :src="'/storage/' + selectedVideo.path" class="w-full h-full object-contain max-h-[70vh]" />
                            </div>

                            <!-- Visor de Audio -->
                            <div v-else-if="selectedVideo.file_type && selectedVideo.file_type.startsWith('audio/')" class="w-full bg-gradient-to-br from-indigo-900 to-black rounded-3xl overflow-hidden shadow-2xl border border-white/10 flex flex-col items-center justify-center min-h-[400px] p-12 text-center">
                                <div class="w-32 h-32 bg-indigo-500/20 rounded-full flex items-center justify-center mb-8 relative">
                                    <div class="absolute inset-0 border-4 border-indigo-500 rounded-full animate-[spin_4s_linear_infinite] border-t-transparent"></div>
                                    <Music class="w-16 h-16 text-indigo-400" />
                                </div>
                                <h3 class="text-3xl font-black text-white mb-2">{{ selectedVideo.title }}</h3>
                                <p class="text-white/40 mb-8 font-mono text-sm">Reproduciendo Audio</p>
                                <audio controls :src="route('video.stream', selectedVideo.id)" class="w-full max-w-lg mt-4 outline-none"></audio>
                            </div>

                            <!-- Visor de PDF -->
                            <div v-else-if="selectedVideo.file_type && selectedVideo.file_type === 'application/pdf'" class="w-full h-[60vh] max-h-[600px] min-h-[400px] bg-gray-900 rounded-3xl overflow-hidden shadow-2xl border border-white/10 relative">
                                <div class="absolute top-4 right-4 z-10">
                                    <a :href="route('video.stream', selectedVideo.id)" download target="_blank" class="p-3 bg-pink-600/80 hover:bg-pink-600 text-white rounded-xl backdrop-blur-md transition flex gap-2 items-center text-sm font-bold shadow-lg">
                                        <Download class="w-4 h-4"/> Descargar
                                    </a>
                                </div>
                                <iframe :src="route('video.stream', selectedVideo.id) + '#toolbar=0'" class="w-full h-full border-0"></iframe>
                            </div>

                            <!-- Visor de Documentos Genéricos -->
                            <div v-else class="w-full bg-gradient-to-br from-gray-900 to-black rounded-3xl overflow-hidden shadow-2xl border border-white/10 flex flex-col items-center justify-center min-h-[400px] p-12 text-center">
                                <div class="w-24 h-24 bg-pink-500/20 rounded-full flex items-center justify-center mb-6">
                                    <FileIcon class="w-12 h-12 text-pink-500" />
                                </div>
                                <h3 class="text-3xl font-black text-white mb-2">{{ selectedVideo.title }}</h3>
                                <p class="text-white/40 mb-8 font-mono text-sm">{{ selectedVideo.file_type || 'Documento Desconocido' }}</p>
                                <a :href="'/storage/' + selectedVideo.path" download target="_blank" class="px-8 py-4 bg-gradient-to-r from-pink-600 to-purple-700 hover:from-pink-500 hover:to-purple-600 text-white font-black rounded-2xl shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-3">
                                    <Download class="w-5 h-5" />
                                    DESCARGAR ARCHIVO
                                </a>
                            </div>

                            <div class="mt-8 px-2 flex justify-between items-end">
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="px-2 py-1 bg-pink-500/20 text-pink-400 text-[10px] font-black rounded-lg uppercase tracking-widest border border-pink-500/20">En Reproducción</span>
                                        <span class="w-1 h-1 bg-white/20 rounded-full"></span>
                                        <span class="text-white/40 text-[10px] font-bold uppercase tracking-widest">{{ category.name }}</span>
                                    </div>
                                    <h3 class="text-3xl font-black text-white tracking-tighter leading-none">{{ selectedVideo.title }}</h3>
                                </div>
                                <div class="flex gap-3">
                                    <button @click="toggleFavorite(selectedVideo)" class="p-4 bg-white/5 rounded-2xl transition-all duration-300 shadow-lg active:scale-95 group/fav" :title="selectedVideo.is_favorite ? 'Quitar de favoritos' : 'Marcar como favorito'">
                                        <Star class="w-6 h-6 transition-all" :class="selectedVideo.is_favorite ? 'fill-yellow-400 text-yellow-400 scale-110' : 'text-white/40 group-hover/fav:text-yellow-400'" />
                                    </button>
                                    <button @click="shareVideo(selectedVideo)" class="p-4 bg-white/5 text-white/40 hover:text-white hover:bg-white/10 rounded-2xl transition-all duration-300 shadow-lg active:scale-95 flex items-center gap-2">
                                        <Share2 class="w-6 h-6" />
                                        <span class="text-xs font-black uppercase tracking-widest hidden sm:inline">Compartir</span>
                                    </button>
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
                            <h4 class="text-2xl font-black text-white mb-2 tracking-tighter uppercase">Selecciona un Archivo</h4>
                            <p class="text-white/30 text-sm max-w-xs font-medium">Explora la galería a la derecha para comenzar tu previsualización privada con la mejor calidad.</p>
                        </div>

                        <!-- Description or Info -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl p-8 group">
                                <div class="flex items-center gap-2 mb-4 text-cyan-400">
                                    <Info class="w-4 h-4" />
                                    <h4 class="text-xs font-black uppercase tracking-widest">Información Técnica</h4>
                                </div>
                                <div class="space-y-4">
                                    <div v-if="selectedVideo?.description" class="p-4 bg-white/5 rounded-xl border border-white/5">
                                        <p class="text-white/80 text-sm leading-relaxed italic">"{{ selectedVideo.description }}"</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-[10px] font-black text-white/20 uppercase mb-1">Tamaño</p>
                                            <p class="text-sm font-black text-white">{{ formatSize(selectedVideo?.file_size || null) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-black text-white/20 uppercase mb-1">Tipo MIME</p>
                                            <p class="text-sm font-black text-white truncate">{{ selectedVideo?.file_type || 'Desconocido' }}</p>
                                        </div>
                                    </div>
                                    <!-- Tags Display -->
                                    <div v-if="selectedVideo?.tags && selectedVideo.tags.length > 0" class="flex flex-wrap gap-2 pt-2">
                                        <div v-for="tag in selectedVideo.tags" :key="tag.id" class="px-2 py-1 bg-cyan-500/10 border border-cyan-500/20 rounded-lg flex items-center gap-1">
                                            <TagIcon class="w-2 h-2 text-cyan-400" />
                                            <span class="text-[9px] font-bold text-cyan-400 uppercase tracking-tighter">{{ tag.name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl p-8">
                                <div class="flex items-center gap-2 mb-4 text-pink-400">
                                    <CheckCircle class="w-4 h-4" />
                                    <h4 class="text-xs font-black uppercase tracking-widest">Verificación</h4>
                                </div>
                                <div class="space-y-2">
                                    <p class="text-white/40 text-xs font-medium">Subido el:</p>
                                    <p class="text-white font-black">{{ selectedVideo ? formatDate(selectedVideo.created_at) : 'N/A' }}</p>
                                    <div class="mt-4 pt-4 border-t border-white/5 flex items-center gap-2">
                                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                        <span class="text-[10px] font-black text-white/20 uppercase">Listo para streaming local</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar / List -->
                    <div class="space-y-6">
                        <div class="bg-gray-950/50 backdrop-blur-xl border border-white/10 rounded-3xl p-6 h-[85vh] sticky top-8 flex flex-col">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                    <PlayCircle class="w-5 h-5 text-pink-500" />
                                    Galería ({{ filteredVideos.length }})
                                </h3>
                                <div class="flex gap-1 bg-white/5 p-1 rounded-lg">
                                    <button @click="showFavoritesOnly = !showFavoritesOnly" :class="showFavoritesOnly ? 'bg-yellow-400/20 text-yellow-400 border border-yellow-400/20' : 'text-white/40'" class="p-1 px-2 rounded-md transition-colors" title="Filtrar Favoritos"><Filter class="w-4 h-4" /></button>
                                    <div class="w-px bg-white/10 mx-0.5"></div>
                                    <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-pink-600 text-white' : 'text-white/40'" class="p-1 px-2 rounded-md transition-colors"><Grid class="w-4 h-4" /></button>
                                    <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-pink-600 text-white' : 'text-white/40'" class="p-1 px-2 rounded-md transition-colors"><ListIcon class="w-4 h-4" /></button>
                                </div>
                            </div>

                            <div class="overflow-y-auto flex-1 custom-scrollbar space-y-3 pr-2">
                                <div 
                                    v-for="(video, index) in filteredVideos" 
                                    :key="video.id"
                                    @click="selectVideo(video)"
                                    class="group relative flex gap-4 p-4 rounded-3xl border cursor-pointer transition-all duration-500 overflow-hidden"
                                    :class="selectedVideo?.id === video.id ? 'bg-pink-500/10 border-pink-500/50 shadow-[0_10px_30px_rgba(236,72,153,0.1)]' : 'bg-white/5 border-white/5 hover:bg-white/10 hover:border-white/10'"
                                >
                                    <div 
                                        class="relative w-28 h-20 flex-shrink-0 bg-black rounded-[1.25rem] overflow-hidden border border-white/10 shadow-lg group-hover:scale-105 transition-transform duration-500"
                                        @mouseenter="playPreview"
                                        @mouseleave="stopPreview"
                                    >
                                        <video 
                                            v-if="video.file_type && video.file_type.startsWith('video/')"
                                            :src="route('video.stream', video.id)" 
                                            class="absolute inset-0 w-full h-full object-contain bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-0"
                                            muted loop playsinline preload="metadata"
                                        ></video>
                                        <img 
                                            v-if="video.thumbnail_path" 
                                            :src="'/storage/' + video.thumbnail_path" 
                                            class="absolute inset-0 w-full h-full object-contain bg-black opacity-80 transition-opacity duration-300 z-10 pointer-events-none"
                                            :class="video.file_type && video.file_type.startsWith('video/') ? 'group-hover:opacity-0' : 'group-hover:opacity-100'"
                                            @error="(e) => (e.target as HTMLImageElement).src = '/images/thumbnail-placeholder.png'"
                                        />
                                        <div v-else class="absolute inset-0 z-10 flex flex-col items-center justify-center w-full h-full bg-gradient-to-br from-gray-800 to-gray-950 text-white/20 transition-opacity duration-300 pointer-events-none" :class="video.file_type && video.file_type.startsWith('video/') ? 'group-hover:opacity-0' : 'group-hover:opacity-100'">
                                            <FileIcon class="w-6 h-6 mb-1 opacity-50" />
                                            <span class="text-[8px] font-black uppercase tracking-widest">Archivo</span>
                                        </div>
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent z-20 pointer-events-none"></div>
                                        <div class="absolute bottom-2 right-2 px-1.5 py-0.5 bg-pink-600/80 backdrop-blur-md text-[8px] text-white font-black rounded-md z-30 pointer-events-none">
                                            #{{ index + 1 }}
                                        </div>
                                        <div v-if="video.is_favorite" class="absolute top-2 left-2 z-30">
                                            <Star class="w-3 h-3 text-yellow-400 fill-yellow-400 drop-shadow-md" />
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

        <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="bg-gray-900 border border-white/20 w-full max-w-lg rounded-3xl p-8 shadow-2xl h-[80vh] flex flex-col">
                <h3 class="text-2xl font-bold text-white mb-6">Subir Archivos</h3>
                
                <!-- Drag and Drop Area -->
                <div 
                    class="border-2 border-dashed border-white/20 rounded-2xl p-8 mb-6 text-center hover:bg-white/5 transition-colors cursor-pointer group flex-shrink-0"
                    @dragover.prevent
                    @drop.prevent="handleDrop"
                    @click="fileInput?.click()"
                >
                    <input type="file" multiple ref="fileInput" @change="handleFileInput" class="hidden" />
                    <Upload class="w-12 h-12 text-pink-500/50 mx-auto mb-4 group-hover:bg-pink-500/20 group-hover:text-pink-500 rounded-full p-2 transition-all" />
                    <p class="text-white font-bold mb-1">Haz clic o arrastra archivos aquí</p>
                    <p class="text-white/40 text-sm">Soporta múltiples videos, imágenes, pdfs, audios...</p>
                </div>

                <!-- Lista de Archivos -->
                <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 mb-6 space-y-3">
                    <div v-if="uploadQueue.length === 0" class="flex flex-col items-center justify-center h-full text-white/20">
                        <FileIcon class="w-16 h-16 mb-4 opacity-20" />
                        <p>No hay archivos seleccionados.</p>
                    </div>

                    <div v-for="(item, idx) in uploadQueue" :key="item.id" class="bg-white/5 border border-white/10 rounded-xl p-4 flex flex-col gap-2 relative">
                        <div class="flex justify-between items-center gap-4">
                            <div class="flex-1 min-w-0">
                                <input v-model="item.name" type="text" class="w-full bg-transparent border-0 border-b border-white/10 text-white text-sm font-bold focus:ring-0 focus:border-pink-500 px-0 py-1" placeholder="Nombre del archivo" :disabled="item.status === 'uploading' || item.status === 'success'" />
                            </div>
                            <button v-if="item.status === 'pending'" @click="removeFile(item.id)" class="text-white/40 hover:text-red-400">
                                <Trash2 class="w-4 h-4" />
                            </button>
                            <span v-else-if="item.status === 'success'" class="text-green-400 text-xs font-bold uppercase">LISTO</span>
                        </div>
                        
                        <div v-if="item.status === 'pending'">
                            <textarea v-model="item.description" rows="1" class="w-full bg-white/5 border border-white/10 rounded-lg text-[10px] text-white/60 focus:ring-pink-500/30 font-medium py-2 px-3 mt-1 resize-none" placeholder="Añadir descripción opcional..."></textarea>
                            <div class="relative mt-1">
                                <TagIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-3 h-3 text-white/20" />
                                <input v-model="item.tags" type="text" class="w-full bg-white/5 border border-white/10 rounded-lg text-[10px] text-white/40 focus:ring-pink-500/30 pl-8 pr-3 py-1.5" placeholder="Etiquetas (separadas por coma)..." />
                            </div>
                        </div>
                        
                        <div v-if="item.status !== 'pending'" class="mt-2 text-[10px] uppercase font-bold tracking-wider" :class="{ 'text-pink-400': item.status === 'uploading', 'text-green-400': item.status === 'success', 'text-red-400': item.status === 'error' }">
                            <div class="flex justify-between mb-1">
                                <span>{{ item.status === 'uploading' ? 'Subiendo...' : (item.status === 'success' ? 'Completado' : 'Error') }}</span>
                                <span v-if="item.status === 'uploading'">{{ item.progress }}%</span>
                            </div>
                            <div class="w-full bg-black/50 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-gradient-to-r h-full transition-all duration-300" 
                                     :class="item.status === 'success' ? 'from-green-500 to-green-400' : (item.status === 'error' ? 'from-red-500 to-red-400' : 'from-pink-500 to-purple-600')"
                                     :style="{ width: item.progress + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botonera -->
                <div class="flex gap-4 pt-4 border-t border-white/10 flex-shrink-0">
                    <button type="button" @click="showUploadModal = false; uploadQueue = []" class="flex-1 py-3 text-white/60 font-bold hover:text-white transition-colors" :disabled="isUploading">Cerrar</button>
                    <button 
                        @click="submitUpload"
                        :disabled="isUploading || uploadQueue.length === 0 || uploadQueue.every(i => i.status === 'success')" 
                        class="flex-1 py-3 bg-gradient-to-r from-pink-600 to-purple-700 disabled:from-gray-800 disabled:to-gray-900 disabled:text-white/20 text-white font-bold rounded-xl shadow-lg transition-all"
                    >
                        {{ isUploading ? 'Subiendo Archivos...' : 'Comenzar Subida' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Share Modal -->
        <div v-if="showShareModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/90 backdrop-blur-md">
            <div class="bg-gray-900 border border-white/20 w-full max-w-md rounded-[2.5rem] p-10 shadow-3xl animate-in zoom-in duration-300">
                <div class="w-16 h-16 bg-pink-500/10 rounded-2xl flex items-center justify-center mb-6 text-pink-500">
                    <Share2 class="w-8 h-8" />
                </div>
                <h3 class="text-3xl font-black text-white mb-2 tracking-tighter">Enlace de Acceso</h3>
                <p class="text-white/40 mb-8 text-sm font-bold uppercase tracking-widest leading-relaxed">Cualquier persona con este link podrá ver el archivo durante 24 horas.</p>
                
                <div class="space-y-6">
                    <div class="relative">
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-4 pr-12 text-sm text-cyan-400 font-mono break-all line-clamp-2">
                            {{ generatedLink }}
                        </div>
                        <button @click="copyLink" class="absolute right-3 top-1/2 -translate-y-1/2 p-2 hover:bg-white/10 rounded-xl transition-all">
                            <Clipboard v-if="!isCopying" class="w-5 h-5 text-white/40" />
                            <CheckCircle v-else class="w-5 h-5 text-green-500" />
                        </button>
                    </div>
                    
                    <button @click="showShareModal = false" class="w-full py-4 bg-white/10 hover:bg-white/20 text-white font-black rounded-2xl transition-all uppercase tracking-widest text-xs">Cerrar</button>
                </div>
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

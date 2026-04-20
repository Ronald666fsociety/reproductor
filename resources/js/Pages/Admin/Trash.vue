<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Trash2, RotateCcw, AlertTriangle, FileIcon, Folder, ShieldAlert } from 'lucide-vue-next';

defineProps<{
    videos: any[];
    categories: any[];
}>();

const restoreVideo = (id: number) => {
    router.post(route('admin.trash.videos.restore', id));
};

const purgeVideo = (id: number) => {
    if (confirm('¿Eliminar permanentemente? Esta acción NO se puede deshacer y borrará el archivo del disco.')) {
        router.delete(route('admin.trash.videos.purge', id));
    }
};

const restoreCategory = (id: number) => {
    router.post(route('admin.trash.categories.restore', id));
};

const purgeCategory = (id: number) => {
    if (confirm('Al purgar la sección se borrará su registro, pero los archivos internos deben purgarse individualmente o quedarían como huérfanos. ¿Proceder?')) {
        router.delete(route('admin.trash.categories.purge', id));
    }
};

const formatSize = (bytes: number) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <Head title="Papelera de Reciclaje" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <div class="p-3 bg-red-500/10 rounded-2xl border border-red-500/20">
                    <Trash2 class="w-6 h-6 text-red-500" />
                </div>
                <div>
                    <h2 class="text-3xl font-black text-white tracking-tighter">Papelera de Reciclaje</h2>
                    <p class="text-white/40 text-xs font-bold uppercase tracking-widest">Recupera o elimina permanentemente tus archivos</p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-12">
                
                <!-- Deleted Categories -->
                <section v-if="categories.length > 0">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <Folder class="w-5 h-5 text-purple-400" />
                        Secciones Eliminadas
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="cat in categories" :key="cat.id" class="glass-premium border border-white/10 rounded-3xl p-6 relative overflow-hidden group">
                            <div class="flex justify-between items-start mb-4">
                                <div class="bg-purple-500/10 p-3 rounded-2xl">
                                    <Folder class="w-6 h-6 text-purple-400" />
                                </div>
                                <div class="flex gap-2">
                                    <button @click="restoreCategory(cat.id)" class="p-2 bg-green-500/10 text-green-500 rounded-xl hover:bg-green-500 hover:text-white transition-all shadow-lg" title="Restaurar">
                                        <RotateCcw class="w-4 h-4" />
                                    </button>
                                    <button @click="purgeCategory(cat.id)" class="p-2 bg-red-500/10 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-all shadow-lg" title="Eliminar para siempre">
                                        <ShieldAlert class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                            <h4 class="text-lg font-black text-white mb-1">{{ cat.name }}</h4>
                            <p class="text-xs text-white/40 font-bold uppercase">{{ cat.videos_count }} archivos contenidos</p>
                            
                            <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-purple-500/5 blur-2xl rounded-full"></div>
                        </div>
                    </div>
                </section>

                <!-- Deleted Videos -->
                <section>
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <FileIcon class="w-5 h-5 text-cyan-400" />
                        Archivos Individuales
                    </h3>
                    
                    <div v-if="videos.length === 0" class="text-center py-20 glass-premium rounded-3xl border border-white/10">
                        <Trash2 class="w-16 h-16 text-white/10 mx-auto mb-4" />
                        <p class="text-white/40 font-bold uppercase tracking-widest">La papelera está vacía</p>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="video in videos" :key="video.id" class="glass-premium border border-white/10 rounded-3xl p-6 flex gap-4 items-center group relative overflow-hidden">
                            <div class="w-20 h-20 bg-black rounded-2xl overflow-hidden flex-shrink-0 border border-white/5 relative">
                                <img v-if="video.thumbnail_path" :src="'/storage/' + video.thumbnail_path" class="w-full h-full object-cover opacity-60" />
                                <div v-else class="w-full h-full flex items-center justify-center bg-gray-900">
                                    <FileIcon class="w-6 h-6 text-white/20" />
                                </div>
                            </div>
                            
                            <div class="min-w-0 flex-1">
                                <h4 class="text-sm font-black text-white truncate">{{ video.title }}</h4>
                                <p class="text-[10px] text-white/30 uppercase font-black mb-2">{{ video.category?.name || 'Sección Desconocida' }}</p>
                                <p class="text-[10px] text-cyan-400/60 font-mono">{{ formatSize(video.file_size) }}</p>
                            </div>

                            <div class="flex flex-col gap-2">
                                <button @click="restoreVideo(video.id)" class="p-2 bg-green-500/10 text-green-500 rounded-lg hover:bg-green-500 hover:text-white transition-all">
                                    <RotateCcw class="w-4 h-4" />
                                </button>
                                <button @click="purgeVideo(video.id)" class="p-2 bg-red-500/10 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition-all">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>

                             <div class="absolute -bottom-4 -right-4 w-20 h-20 bg-red-500/5 blur-2xl rounded-full"></div>
                        </div>
                    </div>
                </section>

                <!-- Warning Message -->
                <div class="p-6 bg-red-950/20 border border-red-500/10 rounded-[2rem] flex items-center gap-6">
                    <div class="p-4 bg-red-500/20 rounded-2xl text-red-500 animate-pulse">
                        <AlertTriangle class="w-8 h-8" />
                    </div>
                    <div>
                        <h4 class="text-lg font-black text-white tracking-tight caps">Importante sobre la Privacidad</h4>
                        <p class="text-sm text-white/40 leading-relaxed max-w-2xl">
                            Los archivos en la papelera siguen ocupando espacio en disco y están ocultos de la biblioteca principal. 
                            Solo un administrador puede ver estos archivos. Pulsa el icono del cubo de basura rojo para eliminarlos permanentemente y liberar espacio.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

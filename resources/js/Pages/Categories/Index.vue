<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Lock, Unlock, FolderPlus, Folder, Trash2, Edit3, PlayCircle, ArrowRight } from 'lucide-vue-next';

interface Category {
    id: number;
    name: string;
    slug: string;
    videos_count: number;
    is_locked: boolean;
}

interface Stats {
    total_used: number;
    disk_total: number;
    disk_free: number;
    file_types: Array<{ file_type: string, total_size: string, count: number }>;
    deleted_count: number;
}

const props = defineProps<{
    categories: Category[];
    stats: Stats;
}>();

const formatSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const diskUsagePercentage = computed(() => {
    if (props.stats.disk_total === 0) return 0;
    const used = props.stats.disk_total - props.stats.disk_free;
    return Math.round((used / props.stats.disk_total) * 100);
});

const vaultUsagePercentage = computed(() => {
    if (props.stats.disk_total === 0) return 0;
    return Math.round((props.stats.total_used / props.stats.disk_total) * 100);
});

const user = (usePage().props.auth as any).user;
const showCreateModal = ref(false);
const showUnlockModal = ref(false);
const selectedCategory = ref<Category | null>(null);

const createForm = useForm({
    name: '',
    password: '',
    order: 0,
});

const unlockForm = useForm({
    password: '',
});

const submitCreate = () => {
    createForm.post(route('categories.store'), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const openUnlock = (category: Category) => {
    selectedCategory.value = category;
    showUnlockModal.value = true;
};

const submitUnlock = () => {
    if (!selectedCategory.value) return;
    unlockForm.post(route('categories.unlock', selectedCategory.value.id), {
        onSuccess: () => {
            showUnlockModal.value = false;
            unlockForm.reset();
        },
    });
};

const deleteCategory = (id: number) => {
    if (confirm('¿Estás seguro de eliminar esta sección y todos sus videos?')) {
        useForm({}).delete(route('categories.destroy', id), {
            preserveScroll: true,
            preserveState: false,
        });
    }
};
</script>

<template>
    <Head title="Secciones Privadas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold bg-gradient-to-r from-pink-500 to-purple-600 bg-clip-text text-transparent">
                    Explorar Contenido
                </h2>
                <button 
                    @click="showCreateModal = true"
                    class="flex items-center gap-2 bg-gradient-to-r from-pink-600 to-purple-700 hover:from-pink-700 hover:to-purple-800 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg transition-all hover:scale-105 active:scale-95"
                >
                    <FolderPlus class="w-4 h-4" />
                    Nueva Sección
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Storage Dashboard -->
                <div class="mb-12 grid grid-cols-1 lg:grid-cols-4 gap-6 animate-in fade-in slide-in-from-top duration-700">
                    <!-- Disk Health Card -->
                    <div class="lg:col-span-2 glass-premium rounded-[2.5rem] p-8 border border-white/10 relative overflow-hidden group">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-[10px] font-black text-cyan-500 uppercase tracking-widest mb-1">Estado de Almacenamiento</h3>
                                <p class="text-3xl font-black text-white tracking-tighter">Capacidad del Disco</p>
                            </div>
                            <div class="p-3 bg-cyan-500/10 rounded-2xl text-cyan-400">
                                <Folder class="w-6 h-6" />
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex justify-between text-sm font-bold">
                                <span class="text-white/40">Uso Total del Sistema</span>
                                <span class="text-white">{{ diskUsagePercentage }}%</span>
                            </div>
                            <div class="w-full bg-white/5 rounded-full h-3 overflow-hidden border border-white/5">
                                <div 
                                    class="bg-gradient-to-r from-cyan-500 to-blue-600 h-full transition-all duration-1000 shadow-[0_0_15px_rgba(34,211,238,0.3)]"
                                    :style="{ width: diskUsagePercentage + '%' }"
                                ></div>
                            </div>
                            <div class="flex justify-between text-[10px] font-black uppercase tracking-wider text-white/30">
                                <span>{{ formatSize(stats.disk_total - stats.disk_free) }} ocupado</span>
                                <span>{{ formatSize(stats.disk_free) }} disponible</span>
                            </div>
                        </div>

                        <!-- Abstract background element -->
                        <div class="absolute -bottom-12 -right-12 w-48 h-48 bg-cyan-500/5 blur-[80px] rounded-full group-hover:bg-cyan-500/10 transition-all"></div>
                    </div>

                    <!-- Vault Specific Stats -->
                    <div class="glass-premium rounded-[2.5rem] p-8 border border-white/10 relative overflow-hidden group">
                        <h3 class="text-[10px] font-black text-pink-500 uppercase tracking-widest mb-1">Tu Bóveda Escaneada</h3>
                        <p class="text-3xl font-black text-white tracking-tighter mb-6">{{ formatSize(stats.total_used) }}</p>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between text-xs font-bold text-white/40">
                                <span>Impacto en Disco</span>
                                <span>{{ vaultUsagePercentage }}%</span>
                            </div>
                            <div class="w-full bg-white/5 rounded-full h-2 overflow-hidden border border-white/5">
                                <div 
                                    class="bg-gradient-to-r from-pink-500 to-purple-600 h-full transition-all duration-1000"
                                    :style="{ width: vaultUsagePercentage + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- File Breakdown QuickView -->
                    <div class="glass-premium rounded-[2.5rem] p-8 border border-white/10 relative overflow-hidden group">
                        <h3 class="text-[10px] font-black text-purple-500 uppercase tracking-widest mb-4">Top Formatos</h3>
                        <div class="space-y-3">
                            <div v-for="type in stats.file_types.slice(0, 3)" :key="type.file_type" class="flex items-center justify-between">
                                <span class="text-xs font-bold text-white/40 truncate max-w-[80px]">{{ type.file_type.split('/')[1]?.toUpperCase() || 'OTRO' }}</span>
                                <span class="text-xs font-black text-white px-2 py-0.5 bg-white/5 rounded-lg">{{ type.count }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Trash Quick Access (Admin Only) -->
                    <Link v-if="user.is_admin" :href="route('admin.trash.index')" class="glass-premium rounded-[2.5rem] p-8 border border-white/10 relative overflow-hidden group border-red-500/20 hover:border-red-500/40 transition-all flex flex-col justify-between">
                        <div>
                            <h3 class="text-[10px] font-black text-red-500 uppercase tracking-widest mb-1">Recuperación</h3>
                            <p class="text-xl font-black text-white tracking-tighter">Papelera</p>
                        </div>
                        <div class="flex items-center justify-between mt-4">
                            <span class="text-sm font-bold text-white/40">{{ stats.deleted_count }} items</span>
                            <Trash2 class="w-5 h-5 text-red-500 group-hover:scale-125 transition-transform" />
                        </div>
                        <div class="absolute -bottom-8 -right-8 w-24 h-24 bg-red-500/5 blur-2xl rounded-full group-hover:bg-red-500/10 transition-all"></div>
                    </Link>
                </div>

                <div v-if="categories.length === 0" class="text-center py-20 bg-white/5 backdrop-blur-md rounded-3xl border border-white/10">
                    <Folder class="w-16 h-16 text-white/20 mx-auto mb-4" />
                    <h3 class="text-xl font-medium text-white">No hay secciones todavía</h3>
                    <p class="text-white/40 mt-2">Comienza agregando una nueva sección desde el panel.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div 
                        v-for="category in categories" 
                        :key="category.id"
                        class="group relative glass-premium rounded-[2.5rem] overflow-hidden transition-all duration-500 hover:scale-[1.03] hover:shadow-[0_20px_50px_rgba(236,72,153,0.15)]"
                    >
                        <!-- Card Border Glow -->
                        <div class="absolute inset-0 border border-white/10 group-hover:border-pink-500/30 rounded-[2.5rem] transition-colors duration-500"></div>

                        <div class="p-8 relative z-10">
                            <div class="flex justify-between items-start mb-6">
                                <div class="p-4 bg-white/5 backdrop-blur-3xl rounded-3xl group-hover:bg-pink-500/10 group-hover:scale-110 transition-all duration-500 border border-white/5">
                                    <Lock v-if="category.is_locked" class="w-7 h-7 text-pink-500" />
                                    <Unlock v-else class="w-7 h-7 text-green-500" />
                                </div>
                                <div class="flex gap-2">
                                    <button @click="deleteCategory(category.id)" class="p-2.5 glass rounded-xl text-white/20 hover:text-red-500 hover:bg-red-500/10 transition-all">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            <h3 class="text-2xl font-black text-white mb-2 tracking-tight group-hover:text-pink-400 transition-colors">{{ category.name }}</h3>
                            <div class="flex items-center gap-2 mb-8">
                                <div class="w-1.5 h-1.5 rounded-full bg-pink-500 animate-pulse"></div>
                                <p class="text-white/40 text-xs font-bold uppercase tracking-widest">{{ category.videos_count }} Contenidos Disponibles</p>
                            </div>

                            <div v-if="category.is_locked">
                                <button 
                                    @click="openUnlock(category)"
                                    class="w-full flex items-center justify-center gap-3 py-4 glass border-white/10 hover:bg-white/10 text-white font-black rounded-2xl transition-all group/btn"
                                >
                                    <Lock class="w-4 h-4 text-pink-500 group-hover/btn:scale-125 transition-transform" />
                                    DESBLOQUEAR AHORA
                                </button>
                            </div>
                            <div v-else>
                                <Link 
                                    :href="route('categories.show', category.id)"
                                    class="w-full flex items-center justify-center gap-3 py-4 bg-gradient-to-r from-pink-600 to-purple-700 text-white font-black rounded-2xl transition-all shadow-lg shadow-pink-900/40 hover:shadow-pink-600/50 group/btn"
                                >
                                    <PlayCircle class="w-5 h-5 group-hover/btn:scale-125 transition-transform" />
                                    ENTRAR A LA SECCIÓN
                                    <ArrowRight class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" />
                                </Link>
                            </div>
                        </div>

                        <!-- Card Decorative Gradient -->
                        <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-pink-600/10 blur-[50px] rounded-full group-hover:bg-pink-600/20 transition-all duration-700"></div>

                        <!-- Gradient Decorative End -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Category Modal (Simulated for speed, but actual implementation would use a proper Modal component) -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
            <div class="glass-premium w-full max-w-md rounded-[2rem] p-10 shadow-3xl border-white/20 animate-in fade-in zoom-in duration-300">
                <h3 class="text-3xl font-black text-white mb-2 tracking-tighter">Nueva Sección</h3>
                <p class="text-white/40 mb-8 text-sm font-bold uppercase tracking-widest">Configura tu nuevo canal de contenido</p>
                <form @submit.prevent="submitCreate" class="space-y-6">
                    <div>
                        <label class="block text-xs font-black text-white/40 uppercase tracking-widest mb-3">Nombre de la Sección</label>
                        <input v-model="createForm.name" type="text" class="w-full bg-white/5 border-white/10 rounded-2xl py-4 px-5 text-white focus:ring-pink-500 focus:border-pink-500 transition-all font-bold placeholder-white/20" placeholder="Ej: Contenido VIP" required />
                    </div>
                    <div>
                        <label class="block text-xs font-black text-white/40 uppercase tracking-widest mb-3">Contraseña Maestra</label>
                        <input v-model="createForm.password" type="password" class="w-full bg-white/5 border-white/10 rounded-2xl py-4 px-5 text-white focus:ring-pink-500 focus:border-pink-500 transition-all font-bold placeholder-white/20" placeholder="••••••••" />
                        <p class="text-[10px] text-white/20 mt-3 font-bold">Opcional: Si no pones clave, la sección será pública para todos los usuarios logueados.</p>
                    </div>
                    <div class="flex gap-4 pt-4">
                        <button type="button" @click="showCreateModal = false" class="flex-1 py-4 text-white/40 font-black hover:text-white transition-colors">CANCELAR</button>
                        <button type="submit" class="flex-1 py-4 bg-gradient-to-r from-pink-600 to-purple-700 text-white font-black rounded-2xl shadow-xl shadow-pink-900/40 hover:scale-[1.05] transition-all">CREAR SECCIÓN</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Unlock Password Modal -->
        <div v-if="showUnlockModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="bg-gray-900 border border-white/20 w-full max-w-md rounded-3xl p-8 shadow-2xl">
                <h3 class="text-2xl font-bold text-white mb-2">Sección Protegida</h3>
                <p class="text-white/40 mb-6 font-medium">Ingresa la contraseña para desbloquear "{{ selectedCategory?.name }}"</p>
                <form @submit.prevent="submitUnlock" class="space-y-4">
                    <div>
                        <input v-model="unlockForm.password" type="password" class="w-full bg-white/5 border-white/10 rounded-xl text-white focus:ring-pink-500" placeholder="••••••••" required autofocus />
                        <div v-if="unlockForm.errors.password" class="text-red-400 text-sm mt-1">{{ unlockForm.errors.password }}</div>
                    </div>
                    <div class="flex gap-4 pt-4">
                        <button type="button" @click="showUnlockModal = false" class="flex-1 py-3 text-white/60 font-bold">Cancelar</button>
                        <button type="submit" class="flex-1 py-3 bg-pink-600 text-white font-bold rounded-xl shadow-lg shadow-pink-900/40">Desbloquear</button>
                    </div>
                </form>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

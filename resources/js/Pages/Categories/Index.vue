<script setup lang="ts">
import { ref } from 'vue';
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

const props = defineProps<{
    categories: Category[];
}>();

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

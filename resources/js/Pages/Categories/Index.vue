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
        useForm({}).delete(route('categories.destroy', id));
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
                    v-if="user.is_admin"
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

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="category in categories" 
                        :key="category.id"
                        class="group relative bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl overflow-hidden hover:border-pink-500/50 transition-all duration-300 hover:shadow-2xl hover:shadow-pink-500/10"
                    >
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-white/5 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                                    <Lock v-if="category.is_locked" class="w-6 h-6 text-pink-400" />
                                    <Unlock v-else class="w-6 h-6 text-green-400" />
                                </div>
                                <div v-if="user.is_admin" class="flex gap-2">
                                    <button @click="deleteCategory(category.id)" class="p-2 text-white/20 hover:text-red-400 transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            <h3 class="text-xl font-bold text-white mb-1">{{ category.name }}</h3>
                            <p class="text-white/40 text-sm mb-6">{{ category.videos_count }} videos disponibles</p>

                            <div v-if="category.is_locked">
                                <button 
                                    @click="openUnlock(category)"
                                    class="w-full flex items-center justify-center gap-2 py-3 bg-white/10 hover:bg-white/20 text-white font-bold rounded-2xl transition-all border border-white/10"
                                >
                                    <Lock class="w-4 h-4" />
                                    Desbloquear Sección
                                </button>
                            </div>
                            <div v-else>
                                <Link 
                                    :href="route('categories.show', category.id)"
                                    class="w-full flex items-center justify-center gap-2 py-3 bg-gradient-to-r from-pink-600 to-purple-700 text-white font-bold rounded-2xl transition-all"
                                >
                                    <PlayCircle class="w-4 h-4" />
                                    Acceder al Contenido
                                    <ArrowRight class="w-4 h-4" />
                                </Link>
                            </div>
                        </div>

                        <!-- Admin Badge -->
                        <div v-if="user.is_admin" class="absolute top-0 right-0 m-4 px-2 py-0.5 bg-yellow-500/20 text-yellow-500 text-[10px] font-bold uppercase rounded-md border border-yellow-500/50">
                            Admin
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Category Modal (Simulated for speed, but actual implementation would use a proper Modal component) -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="bg-gray-900 border border-white/20 w-full max-w-md rounded-3xl p-8 shadow-2xl">
                <h3 class="text-2xl font-bold text-white mb-6">Nueva Sección</h3>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-white/60 mb-2">Nombre de la Sección</label>
                        <input v-model="createForm.name" type="text" class="w-full bg-white/5 border-white/10 rounded-xl text-white focus:ring-pink-500" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white/60 mb-2">Contraseña (opcional)</label>
                        <input v-model="createForm.password" type="password" class="w-full bg-white/5 border-white/10 rounded-xl text-white focus:ring-pink-500" />
                        <p class="text-[10px] text-white/30 mt-1">Deja vacío para acceso sin clave.</p>
                    </div>
                    <div class="flex gap-4 pt-4">
                        <button type="button" @click="showCreateModal = false" class="flex-1 py-3 text-white/60 font-bold">Cancelar</button>
                        <button type="submit" class="flex-1 py-3 bg-pink-600 text-white font-bold rounded-xl shadow-lg shadow-pink-900/40">Crear</button>
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

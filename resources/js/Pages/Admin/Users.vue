<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { UserPlus, Trash2, Users, ShieldAlert, Mail, Lock, User as UserIcon } from 'lucide-vue-next';
import Swal from 'sweetalert2';

interface User {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    created_at: string;
}

const props = defineProps<{
    users: User[];
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
});

const isSubmitting = ref(false);

const createUser = () => {
    isSubmitting.value = true;
    form.post(route('admin.users.store'), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            isSubmitting.value = false;
            form.reset();
            Swal.fire({
                title: '¡Usuario Creado!',
                text: 'El nuevo usuario ya puede ingresar a su espacio.',
                icon: 'success',
                background: '#111827',
                color: '#fff',
                confirmButtonColor: '#ec4899',
            });
        },
        onError: () => {
            isSubmitting.value = false;
        }
    });
};

const deleteUser = (id: number) => {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Se eliminará el usuario y TODO su contenido (categorías y videos).",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#374151',
        confirmButtonText: 'Sí, eliminar todo',
        cancelButtonText: 'Cancelar',
        background: '#111827',
        color: '#fff',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('admin.users.destroy', id), {
                preserveScroll: true,
                preserveState: false,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Eliminado',
                        text: 'El usuario ha sido eliminado del sistema.',
                        icon: 'success',
                        background: '#111827',
                        color: '#fff',
                        confirmButtonColor: '#ec4899',
                    });
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Gestión de Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="p-2 bg-pink-500/20 rounded-xl border border-pink-500/30">
                    <ShieldAlert class="w-6 h-6 text-pink-500" />
                </div>
                <h2 class="font-black text-2xl text-white tracking-tight uppercase">Panel de Configuración</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- Create User Section -->
                <div class="glass-premium rounded-[2rem] p-8 border border-white/5 shadow-2xl relative overflow-hidden">
                    <!-- Glow -->
                    <div class="absolute -top-24 -right-24 w-64 h-64 bg-pink-600/20 rounded-full blur-[80px] pointer-events-none"></div>

                    <div class="flex items-center gap-4 mb-8">
                        <UserPlus class="w-8 h-8 text-pink-400" />
                        <div>
                            <h3 class="text-xl font-black text-white tracking-tighter uppercase">Registrar Nuevo Usuario</h3>
                            <p class="text-white/40 text-sm">Este usuario tendrá un espacio aislado para gestionar sus propias secciones y videos.</p>
                        </div>
                    </div>

                    <form @submit.prevent="createUser" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Nombre -->
                            <div>
                                <label class="block text-xs font-bold text-white/60 uppercase tracking-widest mb-2">Nombre</label>
                                <div class="relative">
                                    <UserIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20" />
                                    <input 
                                        v-model="form.name" 
                                        type="text" 
                                        class="w-full bg-black/40 border-white/10 rounded-2xl py-3 pl-12 pr-4 text-white placeholder-white/20 focus:ring-pink-500 focus:border-pink-500 transition-all font-mono"
                                        placeholder="ej. Juan Pérez"
                                        required
                                    >
                                </div>
                                <div v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</div>
                            </div>
                            
                            <!-- Email -->
                            <div>
                                <label class="block text-xs font-bold text-white/60 uppercase tracking-widest mb-2">Correo</label>
                                <div class="relative">
                                    <Mail class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20" />
                                    <input 
                                        v-model="form.email" 
                                        type="email" 
                                        class="w-full bg-black/40 border-white/10 rounded-2xl py-3 pl-12 pr-4 text-white placeholder-white/20 focus:ring-pink-500 focus:border-pink-500 transition-all font-mono"
                                        placeholder="invitado@correo.com"
                                        required
                                    >
                                </div>
                                <div v-if="form.errors.email" class="text-red-400 text-xs mt-1">{{ form.errors.email }}</div>
                            </div>

                            <!-- Password -->
                            <div>
                                <label class="block text-xs font-bold text-white/60 uppercase tracking-widest mb-2">Contraseña Inicial</label>
                                <div class="relative">
                                    <Lock class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20" />
                                    <input 
                                        v-model="form.password" 
                                        type="text" 
                                        class="w-full bg-black/40 border-white/10 rounded-2xl py-3 pl-12 pr-4 text-white placeholder-white/20 focus:ring-pink-500 focus:border-pink-500 transition-all font-mono"
                                        placeholder="Mínimo 8 caracteres"
                                        required
                                    >
                                </div>
                                <div v-if="form.errors.password" class="text-red-400 text-xs mt-1">{{ form.errors.password }}</div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button 
                                type="submit" 
                                :disabled="isSubmitting"
                                class="flex items-center gap-2 bg-gradient-to-r from-pink-600 to-purple-700 text-white px-8 py-3 rounded-2xl font-black uppercase tracking-widest text-xs hover:scale-105 active:scale-95 transition-all shadow-[0_0_20px_rgba(236,72,153,0.3)] disabled:opacity-50"
                            >
                                <UserPlus class="w-4 h-4" />
                                {{ isSubmitting ? 'Creando...' : 'Registrar Cuenta' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Users List -->
                <div class="glass-premium rounded-[2rem] border border-white/5 shadow-2xl overflow-hidden">
                    <div class="p-6 border-b border-white/5 bg-white/5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <Users class="w-6 h-6 text-white/60" />
                            <h3 class="text-xl font-bold text-white">Cuentas Activas</h3>
                        </div>
                        <span class="px-3 py-1 bg-white/10 rounded-full text-xs text-white/60 font-bold border border-white/10">{{ users.length }} Usuarios</span>
                    </div>
                    
                    <div v-if="users.length === 0" class="p-12 text-center text-white/40 font-medium">
                        Aún no has registrado a ningún usuario adicional.
                    </div>

                    <div v-else class="divide-y divide-white/5">
                        <div v-for="user in users" :key="user.id" class="p-6 flex items-center justify-between hover:bg-white/[0.02] transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-pink-500/20 to-purple-600/20 border border-white/10 flex items-center justify-center text-xl font-black text-pink-400 uppercase">
                                    {{ user.name.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-white">{{ user.name }}</p>
                                    <div class="flex items-center gap-3 mt-1">
                                        <p class="text-sm font-mono text-white/40">{{ user.email }}</p>
                                        <span class="w-1 h-1 bg-white/20 rounded-full"></span>
                                        <p class="text-xs text-white/30 uppercase tracking-widest">Creado el {{ user.created_at }}</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button 
                                    @click="deleteUser(user.id)"
                                    class="p-3 bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white rounded-xl transition-colors shrink-0"
                                    title="Eliminar usuario y su contenido"
                                >
                                    <Trash2 class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

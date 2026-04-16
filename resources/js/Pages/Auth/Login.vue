<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Lock, Mail, Music, Heart } from 'lucide-vue-next';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-cover bg-center bg-no-repeat relative" 
         style="background-image: url('/images/login-bg.png');">
        
        <!-- Overlay for better contrast -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]"></div>

        <Head title="Log in" />

        <div class="z-10 w-full max-w-md px-6">
            <div class="bg-white/10 backdrop-blur-xl border border-white/20 shadow-2xl rounded-3xl p-8 overflow-hidden relative">
                
                <!-- Decorative elements -->
                <div class="absolute top-0 right-0 p-4 opacity-20 transform translate-x-4 -translate-y-4">
                    <Music class="w-16 h-16 text-white" />
                </div>

                <div class="mb-8 text-center">
                    <div class="inline-flex items-center justify-center p-3 mb-4 bg-gradient-to-br from-pink-500 to-purple-600 rounded-2xl shadow-lg">
                        <Heart class="w-6 h-6 text-white" />
                    </div>
                    <h1 class="text-3xl font-bold text-white tracking-tight">Bienvenido</h1>
                    <p class="text-white/60 mt-2">Ingresa al paraíso de contenido privado</p>
                </div>

                <div v-if="status" class="mb-4 text-sm font-medium text-pink-400 text-center">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-white/40 group-focus-within:text-pink-400 transition-colors">
                                <Mail class="w-5 h-5" />
                            </span>
                            <TextInput
                                id="email"
                                type="email"
                                class="pl-11 block w-full bg-white/5 border-white/10 text-white placeholder-white/30 focus:ring-pink-500 focus:border-pink-500 rounded-xl transition-all"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="Email"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-white/40 group-focus-within:text-pink-400 transition-colors">
                                <Lock class="w-5 h-5" />
                            </span>
                            <TextInput
                                id="password"
                                type="password"
                                class="pl-11 block w-full bg-white/5 border-white/10 text-white placeholder-white/30 focus:ring-pink-500 focus:border-pink-500 rounded-xl transition-all"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                placeholder="Contraseña"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer">
                            <Checkbox name="remember" v-model:checked="form.remember" class="rounded border-white/20 bg-white/5 text-pink-600 focus:ring-pink-500" />
                            <span class="ms-2 text-sm text-white/60 hover:text-white transition-colors">Recuérdame</span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm text-pink-400 hover:text-pink-300 transition-colors"
                        >
                            ¿Olvidaste tu contraseña?
                        </Link>
                    </div>

                    <div>
                        <PrimaryButton
                            class="w-full flex justify-center py-3 bg-gradient-to-r from-pink-600 to-purple-700 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 border-none rounded-xl text-lg font-bold shadow-lg shadow-pink-900/40"
                            :class="{ 'opacity-50': form.processing }"
                            :disabled="form.processing"
                        >
                            Entrar Ahora
                        </PrimaryButton>
                    </div>
                </form>

                <div class="mt-8 pt-6 border-t border-white/10 text-center">
                    <p class="text-sm text-white/40">
                        Servidor Privado Premium &bull; Escucha con Estilo
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Transiciones suaves para el fondo */
.bg-cover {
    animation: fadeIn 1s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Tipografía moderna */
:deep(input) {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}
</style>

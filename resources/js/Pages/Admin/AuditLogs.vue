<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Activity, Search, User as UserIcon, Calendar, Info, ShieldCheck, MapPin } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps<{
    logs: any;
    users: any[];
    filters: any;
}>();

const search = ref(props.filters.search || '');
const user_id = ref(props.filters.user_id || '');
const action = ref(props.filters.action || '');

const updateFilters = debounce(() => {
    router.get(route('admin.audit-logs.index'), {
        search: search.value,
        user_id: user_id.value,
        action: action.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}, 500);

watch([search, user_id, action], () => {
    updateFilters();
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getActionColor = (action: string) => {
    switch (action) {
        case 'created': return 'text-green-400 bg-green-400/10';
        case 'deleted': return 'text-red-400 bg-red-400/10';
        case 'updated': return 'text-yellow-400 bg-yellow-400/10';
        case 'restored': return 'text-cyan-400 bg-cyan-400/10';
        case 'force_deleted': return 'text-red-600 bg-red-600/20';
        default: return 'text-white/40 bg-white/5';
    }
};
</script>

<template>
    <Head title="Historial de Auditoría" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-cyan-500/10 rounded-2xl border border-cyan-500/20">
                        <Activity class="w-6 h-6 text-cyan-500" />
                    </div>
                    <div>
                        <h2 class="text-3xl font-black text-white tracking-tighter">Historial de Auditoría</h2>
                        <p class="text-white/40 text-xs font-bold uppercase tracking-widest">Monitoreo de seguridad y acciones en tiempo real</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-4">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/20" />
                        <input v-model="search" type="text" placeholder="Buscar en logs..." class="bg-white/5 border-white/10 rounded-xl pl-10 pr-4 py-2 text-sm text-white focus:ring-cyan-500 w-48" />
                    </div>
                    <select v-model="user_id" class="bg-white/5 border-white/10 rounded-xl px-4 py-2 text-sm text-white focus:ring-cyan-500">
                        <option value="">Todos los usuarios</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>
                    <select v-model="action" class="bg-white/5 border-white/10 rounded-xl px-4 py-2 text-sm text-white focus:ring-cyan-500">
                        <option value="">Todas las acciones</option>
                        <option value="created">Creación</option>
                        <option value="updated">Edición</option>
                        <option value="deleted">Borrado</option>
                        <option value="restored">Restauración</option>
                    </select>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="glass-premium rounded-[2.5rem] border border-white/10 overflow-hidden shadow-2xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-white/5 border-b border-white/10 uppercase tracking-widest text-[10px] font-black text-white/40">
                                    <th class="px-8 py-5">Usuario</th>
                                    <th class="px-8 py-5">Acción</th>
                                    <th class="px-8 py-5">Descripción</th>
                                    <th class="px-8 py-5">Ubicación / IP</th>
                                    <th class="px-8 py-5">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="log in logs.data" :key="log.id" class="hover:bg-white/5 transition-colors group">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-[10px] font-black border border-white/10">
                                                {{ log.user.name[0].toUpperCase() }}
                                            </div>
                                            <span class="text-sm font-bold text-white/80">{{ log.user.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter" :class="getActionColor(log.action)">
                                            {{ log.action.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <p class="text-sm text-white/60 max-w-sm leading-relaxed">{{ log.description }}</p>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex flex-col gap-1">
                                            <div class="flex items-center gap-2 text-white/40">
                                                <MapPin class="w-3 h-3" />
                                                <span class="text-[10px] font-mono">{{ log.ip_address }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-white/20">
                                                <Info class="w-3 h-3" />
                                                <span class="text-[8px] truncate max-w-[120px]">{{ log.user_agent }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-2 text-white/40 text-xs">
                                            <Calendar class="w-3 h-3" />
                                            {{ formatDate(log.created_at) }}
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="logs.data.length === 0">
                                    <td colspan="5" class="px-8 py-20 text-center">
                                        <Activity class="w-12 h-12 text-white/5 mx-auto mb-4" />
                                        <p class="text-white/20 font-bold uppercase tracking-widest text-xs">No se encontraron registros de actividad</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="logs.last_page > 1" class="p-6 border-t border-white/5 bg-white/5 flex justify-center gap-2">
                        <button 
                            v-for="link in logs.links" 
                            :key="link.label"
                            @click="link.url ? router.get(link.url) : null"
                            class="px-4 py-2 rounded-xl text-xs font-black transition-all"
                            :class="link.active ? 'bg-cyan-500 text-white shadow-lg shadow-cyan-500/30' : 'text-white/40 hover:bg-white/10 hover:text-white'"
                            v-html="link.label"
                        ></button>
                    </div>
                </div>

                <!-- Footer Summary Card -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="glass-premium p-6 border border-white/10 rounded-3xl flex items-center gap-6">
                        <div class="p-4 bg-green-500/20 rounded-2xl text-green-500">
                            <ShieldCheck class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-white/20 uppercase tracking-widest mb-1">Detección de Amenazas</p>
                            <p class="text-lg font-black text-white">Sistema Protegido</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import VueApexCharts from 'vue3-apexcharts';
import { LayoutDashboard, HardDrive, Files, Users, Activity, User } from 'lucide-vue-next';

const props = defineProps<{
    stats: {
        totalStorage: number;
        totalFiles: number;
        totalUsers: number;
    };
    storageByType: {
        video: number;
        image: number;
        application: number;
        audio: number;
        other: number;
    };
    recentActivity: any[];
    topUsers: any[];
}>();

const formatSize = (bytes: number) => {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Chart Options
const chartSeries = ref([
    props.storageByType.video,
    props.storageByType.image,
    props.storageByType.application,
    props.storageByType.audio,
    props.storageByType.other
]);

const chartOptions = ref({
    chart: { type: 'donut', background: 'transparent' },
    labels: ['Videos', 'Imágenes', 'Documentos (PDF)', 'Audio', 'Otros'],
    colors: ['#06b6d4', '#ec4899', '#f59e0b', '#8b5cf6', '#64748b'],
    theme: { mode: 'dark' },
    stroke: { show: false },
    dataLabels: { enabled: false },
    plotOptions: {
        pie: {
            donut: {
                size: '75%',
                labels: {
                    show: true,
                    name: { color: '#ffffff', fontSize: '14px', fontFamily: 'Inter' },
                    value: { color: '#ffffff', fontSize: '24px', fontFamily: 'Inter', formatter: (val: number) => formatSize(val) },
                    total: { show: true, label: 'Total Usado', color: '#94a3b8', formatter: () => formatSize(props.stats.totalStorage) }
                }
            }
        }
    },
    legend: { position: 'bottom', labels: { colors: '#94a3b8' } },
    tooltip: {
        y: { formatter: (val: number) => formatSize(val) }
    }
});

const getActionColor = (action: string) => {
    if (action.includes('created') || action.includes('uploaded')) return 'text-green-400 bg-green-400/10';
    if (action.includes('deleted') || action.includes('purged')) return 'text-red-400 bg-red-400/10';
    if (action.includes('updated') || action.includes('restored')) return 'text-amber-400 bg-amber-400/10';
    return 'text-blue-400 bg-blue-400/10';
};
</script>

<template>
    <Head title="Dashboard Analítico" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <div class="p-3 bg-cyan-500/10 rounded-2xl border border-cyan-500/20">
                    <LayoutDashboard class="w-6 h-6 text-cyan-400" />
                </div>
                <div>
                    <h2 class="text-3xl font-black text-white tracking-tighter">Panel de Control</h2>
                    <p class="text-white/40 text-xs font-bold uppercase tracking-widest">Estadísticas y Analíticas Globales</p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- Stat Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="glass-premium border border-white/10 rounded-3xl p-6 relative overflow-hidden group">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-white/60 font-bold uppercase tracking-wider text-xs">Almacenamiento Total</h3>
                            <div class="p-2 bg-blue-500/10 rounded-xl"><HardDrive class="w-5 h-5 text-blue-400" /></div>
                        </div>
                        <p class="text-4xl font-black text-white">{{ formatSize(stats.totalStorage) }}</p>
                        <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-blue-500/10 blur-2xl rounded-full"></div>
                    </div>
                    
                    <div class="glass-premium border border-white/10 rounded-3xl p-6 relative overflow-hidden group">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-white/60 font-bold uppercase tracking-wider text-xs">Total de Archivos</h3>
                            <div class="p-2 bg-purple-500/10 rounded-xl"><Files class="w-5 h-5 text-purple-400" /></div>
                        </div>
                        <p class="text-4xl font-black text-white">{{ stats.totalFiles }}</p>
                        <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-purple-500/10 blur-2xl rounded-full"></div>
                    </div>
                    
                    <div class="glass-premium border border-white/10 rounded-3xl p-6 relative overflow-hidden group">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-white/60 font-bold uppercase tracking-wider text-xs">Usuarios Activos</h3>
                            <div class="p-2 bg-pink-500/10 rounded-xl"><Users class="w-5 h-5 text-pink-400" /></div>
                        </div>
                        <p class="text-4xl font-black text-white">{{ stats.totalUsers }}</p>
                        <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-pink-500/10 blur-2xl rounded-full"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Storage Chart -->
                    <div class="glass-premium border border-white/10 rounded-3xl p-6 relative lg:col-span-1 flex flex-col">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <HardDrive class="w-5 h-5 text-cyan-400" />
                            Uso por Tipo de Archivo
                        </h3>
                        <div class="flex-1 flex items-center justify-center min-h-[300px]">
                            <VueApexCharts v-if="stats.totalStorage > 0" type="donut" width="100%" :options="chartOptions" :series="chartSeries" />
                            <p v-else class="text-white/40 font-bold text-sm text-center">No hay archivos para analizar</p>
                        </div>
                    </div>

                    <!-- Top Users -->
                    <div class="glass-premium border border-white/10 rounded-3xl p-6 relative lg:col-span-2">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <Users class="w-5 h-5 text-pink-400" />
                            Usuarios con Mayor Uso
                        </h3>
                        
                        <div class="space-y-4">
                            <div v-for="(user, index) in topUsers" :key="index" class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/5 hover:bg-white/10 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-cyan-500 to-blue-500 flex items-center justify-center text-white font-bold">
                                        {{ user.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="text-white font-bold">{{ user.name }}</p>
                                        <p class="text-white/50 text-xs">{{ user.email }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-cyan-400 font-bold">{{ formatSize(user.used_storage) }}</p>
                                    <p class="text-white/40 text-xs">
                                        Cuota: <span class="text-white">{{ user.storage_quota ? formatSize(user.storage_quota) : 'Ilimitada' }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Table -->
                <div class="glass-premium border border-white/10 rounded-3xl p-6 relative overflow-hidden">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <Activity class="w-5 h-5 text-green-400" />
                        Actividad Reciente Global
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-white/40 text-xs uppercase tracking-wider border-b border-white/5">
                                    <th class="pb-3 px-4 font-bold">Usuario</th>
                                    <th class="pb-3 px-4 font-bold">Acción</th>
                                    <th class="pb-3 px-4 font-bold">Detalle</th>
                                    <th class="pb-3 px-4 font-bold text-right">Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in recentActivity" :key="log.id" class="border-b border-white/5 hover:bg-white/5 transition-colors">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-2 text-white font-medium text-sm">
                                            <User class="w-4 h-4 text-white/50" />
                                            {{ log.user?.name || 'Sistema' }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span :class="['px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider', getActionColor(log.action)]">
                                            {{ log.action }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-white/70 text-sm">
                                        {{ log.description }}
                                    </td>
                                    <td class="py-4 px-4 text-right text-white/40 text-xs font-mono">
                                        {{ new Date(log.created_at).toLocaleString() }}
                                    </td>
                                </tr>
                                <tr v-if="recentActivity.length === 0">
                                    <td colspan="4" class="text-center py-8 text-white/40">No hay actividad reciente</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

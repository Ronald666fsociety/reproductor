<script setup lang="ts">
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import { Home, Folder, User as UserIcon, LogOut, Search, Bell, X, FileIcon, ChevronRight, Trash2, Activity, LayoutDashboard } from 'lucide-vue-next';
import axios from 'axios';

const showingNavigationDropdown = ref(false);
const searchQuery = ref('');
const searchResults = ref<{categories: any[], files: any[]}>({categories: [], files: []});
const isSearching = ref(false);
const showSearchResults = ref(false);

const performSearch = async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = {categories: [], files: []};
        showSearchResults.value = false;
        return;
    }

    isSearching.value = true;
    try {
        const response = await axios.get(route('api.search'), { params: { q: searchQuery.value } });
        searchResults.value = response.data;
        showSearchResults.value = true;
    } catch (error) {
        console.error('Error searching:', error);
    } finally {
        isSearching.value = false;
    }
};

const clearSearch = () => {
    searchQuery.value = '';
    searchResults.value = {categories: [], files: []};
    showSearchResults.value = false;
};
</script>

<template>
    <div class="min-h-screen text-white selection:bg-cyan-500/30 relative bg-[#0B0C10] overflow-x-hidden">
        
        <!-- Animated Galaxy Background -->
        <div class="fixed inset-0 pointer-events-none z-0">
            <!-- Espacio Profundo Gradiente -->
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-900/40 via-[#0B0C10] to-[#0B0C10]"></div>
            
            <!-- Nebulosas Glowing -->
            <div class="absolute -top-[20%] -left-[10%] w-[60%] h-[60%] bg-purple-600/20 blur-[150px] rounded-full animate-pulse" style="animation-duration: 8s;"></div>
            <div class="absolute top-[40%] -right-[20%] w-[50%] h-[50%] bg-cyan-600/10 blur-[120px] rounded-full animate-pulse" style="animation-duration: 12s;"></div>
            <div class="absolute bottom-0 left-[20%] w-[40%] h-[40%] bg-blue-600/10 blur-[150px] rounded-full animate-pulse" style="animation-duration: 10s;"></div>

            <!-- Estrellas (CSS Background) -->
            <div class="stars-layer absolute inset-0 opacity-50"></div>
        </div>

        <div class="relative z-10">
            <nav class="sticky top-0 z-40 bg-[#0B0C10]/60 backdrop-blur-2xl border-b border-cyan-500/10 shadow-[0_4px_30px_rgba(0,255,255,0.05)]">
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-20 justify-between items-center">
                        <div class="flex items-center gap-10">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('categories.index')" class="transition-transform hover:scale-110">
                                    <div class="w-12 h-12 bg-gradient-to-br from-cyan-400 via-blue-500 to-purple-600 rounded-[1rem] flex items-center justify-center shadow-lg shadow-cyan-500/30 border border-white/20">
                                        <Home class="w-6 h-6 text-white" fill="currentColor" fill-opacity="0.2" />
                                    </div>
                                </Link>
                                <span class="ml-4 font-black text-2xl tracking-tighter hidden sm:block bg-gradient-to-r from-white to-white/60 bg-clip-text text-transparent">SKY<span class="text-cyan-400">VAULT</span></span>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-2 sm:flex">
                                <NavLink
                                    :href="route('categories.index')"
                                    :active="route().current('categories.*')"
                                    class="px-4 py-2 rounded-xl transition-all"
                                >
                                    <Folder class="w-4 h-4 mr-2" />
                                    Secciones
                                </NavLink>
                                    <NavLink
                                        v-if="$page.props.auth.user.is_admin"
                                        :href="route('admin.dashboard')"
                                        :active="route().current('admin.dashboard')"
                                        class="px-4 py-2 rounded-xl transition-all"
                                    >
                                        <LayoutDashboard class="w-4 h-4 mr-2" />
                                        Dashboard
                                    </NavLink>
                                    <NavLink
                                        v-if="$page.props.auth.user.is_admin"
                                        :href="route('admin.users.index')"
                                        :active="route().current('admin.users.*')"
                                        class="px-4 py-2 rounded-xl transition-all"
                                    >
                                        <UserIcon class="w-4 h-4 mr-2" />
                                        Usuarios
                                    </NavLink>
                                    <NavLink
                                        v-if="$page.props.auth.user.is_admin"
                                        :href="route('admin.trash.index')"
                                        :active="route().current('admin.trash.*')"
                                        class="px-4 py-2 rounded-xl transition-all text-red-400 hover:text-red-300"
                                    >
                                        <Trash2 class="w-4 h-4 mr-2" />
                                        Papelera
                                    </NavLink>
                                    <NavLink
                                        v-if="$page.props.auth.user.is_admin"
                                        :href="route('admin.audit-logs.index')"
                                        :active="route().current('admin.audit-logs.*')"
                                        class="px-4 py-2 rounded-xl transition-all text-cyan-400"
                                    >
                                        <Activity class="w-4 h-4 mr-2" />
                                        Auditoría
                                    </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center gap-6">
                            <!-- Search Component -->
                            <div class="relative hidden lg:block group">
                                <div class="relative">
                                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 transition-colors" :class="searchQuery ? 'text-cyan-400' : 'text-white/30'" />
                                    <input 
                                        v-model="searchQuery"
                                        @input="performSearch"
                                        @focus="showSearchResults = searchQuery.length >= 2"
                                        type="text" 
                                        placeholder="Buscar en la bóveda..." 
                                        class="bg-cyan-950/20 border-cyan-500/20 rounded-full py-2.5 pl-10 pr-10 text-sm focus:ring-cyan-500/50 focus:border-cyan-500/50 w-64 lg:w-80 transition-all placeholder:text-cyan-200/30 text-cyan-50 shadow-inner" 
                                    />
                                    <button v-if="searchQuery" @click="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/20 hover:text-white transition-colors">
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>

                                <!-- Search Results Dropdown -->
                                <div v-if="showSearchResults" v-click-outside="() => showSearchResults = false" class="absolute top-full mt-3 right-0 w-[400px] bg-[#0d1117] border border-white/10 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.5)] overflow-hidden backdrop-blur-3xl z-[60] animate-in fade-in slide-in-from-top-2 duration-200">
                                    <div class="p-4 overflow-y-auto max-h-[70vh]">
                                        <!-- Categories Section -->
                                        <div v-if="searchResults.categories.length > 0" class="mb-4">
                                            <h4 class="text-[10px] font-black text-cyan-500 uppercase tracking-widest px-2 mb-2">Canales / Secciones</h4>
                                            <div class="grid gap-1">
                                                <Link 
                                                    v-for="cat in searchResults.categories" 
                                                    :key="cat.id" 
                                                    :href="route('categories.show', cat.id)"
                                                    @click="showSearchResults = false"
                                                    class="flex items-center justify-between p-3 rounded-xl hover:bg-white/5 group/res transition-colors"
                                                >
                                                    <div class="flex items-center gap-3">
                                                        <div class="p-2 bg-cyan-500/10 rounded-lg group-hover/res:bg-cyan-500/20">
                                                            <Folder class="w-4 h-4 text-cyan-400" />
                                                        </div>
                                                        <span class="text-sm font-bold text-white/80 group-hover/res:text-white">{{ cat.name }}</span>
                                                    </div>
                                                    <ChevronRight class="w-4 h-4 text-white/20 group-hover/res:text-white group-hover/res:translate-x-1 transition-all" />
                                                </Link>
                                            </div>
                                        </div>

                                        <!-- Files Section -->
                                        <div v-if="searchResults.files.length > 0">
                                            <h4 class="text-[10px] font-black text-pink-500 uppercase tracking-widest px-2 mb-2">Archivos / Medios</h4>
                                            <div class="grid gap-1">
                                                <Link 
                                                    v-for="file in searchResults.files" 
                                                    :key="file.id" 
                                                    :href="route('categories.show', file.category_id)"
                                                    @click="showSearchResults = false"
                                                    class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/5 group/res transition-colors"
                                                >
                                                    <div class="w-12 h-10 bg-black rounded-lg overflow-hidden flex-shrink-0 border border-white/10 relative">
                                                        <img v-if="file.thumbnail_path" :src="'/storage/' + file.thumbnail_path" class="w-full h-full object-cover opacity-60" />
                                                        <div v-else class="w-full h-full flex items-center justify-center bg-gray-800">
                                                            <FileIcon class="w-4 h-4 text-white/20" />
                                                        </div>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="text-sm font-bold text-white/80 group-hover/res:text-white truncate">{{ file.title }}</p>
                                                        <p class="text-[10px] font-bold text-white/20 uppercase">{{ file.category.name }}</p>
                                                    </div>
                                                </Link>
                                            </div>
                                        </div>

                                        <!-- Empty State -->
                                        <div v-if="searchResults.categories.length === 0 && searchResults.files.length === 0" class="py-12 text-center">
                                            <Search class="w-10 h-10 text-white/10 mx-auto mb-3" />
                                            <p class="text-sm font-bold text-white/30 uppercase tracking-widest">No se encontraron resultados</p>
                                        </div>
                                    </div>
                                    
                                    <div class="p-3 bg-white/5 border-t border-white/5 flex justify-between items-center">
                                        <span class="text-[9px] font-bold text-white/20 uppercase tracking-tighter">Enter para ver todo</span>
                                        <span class="text-[9px] font-bold text-white/20 uppercase tracking-tighter">{{ searchResults.categories.length + searchResults.files.length }} coincidencias</span>
                                    </div>
                                </div>
                            </div>

                            <button class="text-white/40 hover:text-white transition-colors relative">
                                <Bell class="w-5 h-5" />
                                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-cyan-400 rounded-full border-2 border-[#0B0C10] shadow-[0_0_10px_#22d3ee]"></span>
                            </button>

                            <!-- Settings Dropdown -->
                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button type="button" class="flex items-center gap-3 p-1.5 pl-4 bg-white/5 border border-white/10 rounded-full hover:bg-white/10 transition-all group">
                                            <span class="text-sm font-bold text-white/80 group-hover:text-white">{{ $page.props.auth.user.name }}</span>
                                            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-sm font-black border-2 border-cyan-400/30 shadow-[0_0_15px_rgba(99,102,241,0.4)]">
                                                {{ $page.props.auth.user.name[0].toUpperCase() }}
                                            </div>
                                        </button>
                                    </template>

                                    <template #content>
                                        <div class="bg-slate-900 border border-white/10 rounded-2xl shadow-2xl overflow-hidden p-2">
                                            <DropdownLink :href="route('profile.edit')" class="flex items-center gap-3 rounded-xl hover:bg-white/5 px-4 py-3 text-white/70 hover:text-white">
                                                <UserIcon class="w-4 h-4" /> Perfil
                                            </DropdownLink>
                                            <div class="h-px bg-white/5 my-1"></div>
                                            <DropdownLink :href="route('logout')" method="post" as="button" class="w-full flex items-center gap-3 rounded-xl hover:bg-red-500/10 px-4 py-3 text-red-400 hover:text-red-300 text-left">
                                                <LogOut class="w-4 h-4" /> Cerrar Sesión
                                            </DropdownLink>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="p-2 text-white/60 hover:text-white transition-colors">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden bg-slate-900 border-t border-white/5">
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink :href="route('categories.index')" :active="route().current('categories.*')">
                            Secciones
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="$page.props.auth.user.is_admin" :href="route('admin.dashboard')" :active="route().current('admin.dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="$page.props.auth.user.is_admin" :href="route('admin.users.index')" :active="route().current('admin.users.*')">
                            Usuarios
                        </ResponsiveNavLink>
                    </div>

                    <div class="border-t border-white/5 pb-1 pt-4">
                        <div class="px-4 flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-pink-500/20 flex items-center justify-center text-pink-500 font-bold">
                                {{ $page.props.auth.user.name[0].toUpperCase() }}
                            </div>
                            <div>
                                <div class="text-base font-bold text-white">{{ $page.props.auth.user.name }}</div>
                                <div class="text-sm font-medium text-white/40">{{ $page.props.auth.user.email }}</div>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')"> Perfil </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button" class="text-red-400"> Cerrar Sesión </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-transparent" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="pb-20">
                <slot />
            </main>

            <!-- Footer -->
            <footer class="mt-auto py-10 border-t border-white/5 text-center">
                <p class="text-white/20 text-xs tracking-widest font-bold uppercase">Private Content Server &bull; {{ new Date().getFullYear() }}</p>
            </footer>
        </div>
    </div>
</template>

<style>
/* Animated Stars Layer */
.stars-layer {
    background-image: 
        radial-gradient(2px 2px at 20px 30px, #ffffff, rgba(0,0,0,0)),
        radial-gradient(2px 2px at 40px 70px, #ffffff, rgba(0,0,0,0)),
        radial-gradient(2px 2px at 50px 160px, #ffffff, rgba(0,0,0,0)),
        radial-gradient(2px 2px at 90px 40px, #ffffff, rgba(0,0,0,0)),
        radial-gradient(2px 2px at 130px 80px, #ffffff, rgba(0,0,0,0)),
        radial-gradient(2px 2px at 160px 120px, #ffffff, rgba(0,0,0,0));
    background-repeat: repeat;
    background-size: 200px 200px;
    animation: stars-drift 100s linear infinite;
}

@keyframes stars-drift {
    0% { background-position: 0 0; }
    100% { background-position: -1000px 1000px; }
}

.dark body {
    background-color: #0B0C10;
}
</style>

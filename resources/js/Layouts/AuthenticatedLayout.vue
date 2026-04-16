<script setup lang="ts">
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import { Home, Folder, User as UserIcon, LogOut, Search, Bell } from 'lucide-vue-next';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-white selection:bg-pink-500/30">
        <!-- Dashboard Background Decorations -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-purple-600/10 blur-[120px] rounded-full"></div>
            <div class="absolute top-[20%] -right-[10%] w-[30%] h-[50%] bg-pink-600/10 blur-[100px] rounded-full"></div>
        </div>

        <div class="relative z-10">
            <nav class="sticky top-0 z-40 bg-slate-950/60 backdrop-blur-xl border-b border-white/5">
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-20 justify-between items-center">
                        <div class="flex items-center gap-10">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('categories.index')" class="transition-transform hover:scale-105">
                                    <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-pink-500/20">
                                        <Home class="w-6 h-6 text-white" />
                                    </div>
                                </Link>
                                <span class="ml-3 font-black text-xl tracking-tighter hidden sm:block">REPRODUCTOR<span class="text-pink-500">.</span></span>
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
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center gap-6">
                            <!-- Search (Decorative) -->
                            <div class="relative hidden lg:block">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" />
                                <input type="text" placeholder="Buscar..." class="bg-white/5 border-white/10 rounded-full py-2 pl-10 pr-4 text-sm focus:ring-pink-500/50 focus:border-pink-500/50 w-64 transition-all" />
                            </div>

                            <button class="text-white/40 hover:text-white transition-colors relative">
                                <Bell class="w-5 h-5" />
                                <span class="absolute -top-1 -right-1 w-2 h-2 bg-pink-500 rounded-full border-2 border-slate-950"></span>
                            </button>

                            <!-- Settings Dropdown -->
                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button type="button" class="flex items-center gap-3 p-1.5 pl-4 bg-white/5 border border-white/10 rounded-full hover:bg-white/10 transition-all group">
                                            <span class="text-sm font-bold text-white/80 group-hover:text-white">{{ $page.props.auth.user.name }}</span>
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-slate-700 to-slate-800 flex items-center justify-center text-xs font-bold border border-white/10">
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
/* Reset base dark mode colors for specific components if needed */
.dark body {
    background-color: #020617;
}
</style>

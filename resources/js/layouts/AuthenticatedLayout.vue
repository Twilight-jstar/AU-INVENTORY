<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'; 
import { Link, usePage } from '@inertiajs/vue3'; 
import { 
    Package, 
    Tags, 
    Scale, 
    History as HistoryIcon, 
    LayoutDashboard,
    LogOut,
    Menu,
    X,
    User,
    ChevronUp 
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

const isMobileMenuOpen = ref(false);
const showUserMenu = ref(false); 
const page = usePage();

const userName = computed(() => page.props.auth.user.name);

const navItems = [
    { name: 'Dashboard', href: route('dashboard'), icon: LayoutDashboard, active: 'dashboard' },
    { name: 'Inventory Items', href: route('items.index'), icon: Package, active: 'items.*' },
    { name: 'Asset Categories', href: route('categories.index'), icon: Tags, active: 'categories.*' },
    { name: 'Measurement Units', href: route('units.index'), icon: Scale, active: 'units.*' },
    { name: 'Audit Trail', href: route('transactions.index'), icon: HistoryIcon, active: 'transactions.*' },
];

const pageTitle = computed(() => {
    if (page.props.title) return page.props.title;
    const activeNav = navItems.find(item => route().current(item.active));
    return activeNav ? activeNav.name : 'Inventory Management';
});

const closeUserMenu = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    if (!target.closest('.user-menu-container')) {
        showUserMenu.value = false;
    }
};

onMounted(() => window.addEventListener('click', closeUserMenu));
onUnmounted(() => window.removeEventListener('click', closeUserMenu));
</script>

<template>
    <div class="min-h-screen flex flex-col md:flex-row relative bg-slate-50">
        <div 
            class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat opacity-20 pointer-events-none"
            style="background-image: url('/images/bg.png');"
        ></div>

        <aside class="hidden md:flex flex-col w-64 bg-purple-900 sticky top-0 h-screen z-20 shadow-2xl border-r border-purple-800">
            <div class="p-8">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-2 text-white">
                        <Package class="w-7 h-7 text-purple-300" />
                        <span class="font-bold tracking-tight text-xl">ALF Inventory</span>
                    </div>
                    <p class="text-[10px] text-purple-300/60 font-bold uppercase tracking-[0.2em]">Disbursement System</p>
                </div>
            </div>

            <nav class="flex-1 px-4 space-y-1.5 mt-4">
                <Link 
                    v-for="item in navItems" 
                    :key="item.name"
                    :href="item.href"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 group"
                    :class="route().current(item.active) 
                        ? 'bg-white/15 text-white shadow-inner border border-white/10' 
                        : 'text-purple-100/70 hover:bg-white/5 hover:text-white'"
                >
                    <component :is="item.icon" class="w-4 h-4 opacity-70 group-hover:opacity-100 transition-opacity" />
                    {{ item.name }}
                </Link>
            </nav>

            <div class="p-4 user-menu-container border-t border-purple-800">
                <div class="p-3 rounded-2xl flex items-center justify-between hover:bg-white/5 transition-colors cursor-pointer" @click.stop="showUserMenu = !showUserMenu">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="shrink-0 w-8 h-8 rounded-lg bg-purple-700 border border-purple-500 flex items-center justify-center text-purple-100 shadow-sm">
                            <User class="w-4 h-4" />
                        </div>
                        <div class="flex flex-col overflow-hidden">
                            <span class="text-xs text-white font-bold truncate">{{ userName }}</span>
                            <span class="text-[9px] text-purple-300/60 font-bold uppercase tracking-widest">USER</span>
                        </div>
                    </div>
                    <ChevronUp class="w-4 h-4 text-purple-300 transition-transform" :class="{'rotate-180': showUserMenu}" />
                </div>

                <Transition name="pop">
                    <div v-if="showUserMenu" class="absolute bottom-20 left-4 right-4 bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden z-50">
                        <Link 
                            :href="route('logout')" 
                            method="post" 
                            as="button"
                            class="flex w-full items-center gap-3 px-4 py-3 text-xs font-bold text-red-600 hover:bg-red-50 transition-colors"
                        >
                            <LogOut class="w-4 h-4" />
                            Log Out of System
                        </Link>
                    </div>
                </Transition>
            </div>
        </aside>

        <main class="flex-1 relative z-10 flex flex-col h-screen overflow-hidden">
            <header class="w-full bg-white/70 backdrop-blur-xl border-b border-slate-200 px-6 md:px-10 py-5">
                <div class="max-w-7xl mx-auto flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 tracking-tight leading-none">
                            {{ pageTitle }}
                        </h2>
                        <div class="flex items-center gap-2 mt-1.5">
                            <span class="h-1.5 w-1.5 rounded-full bg-purple-600"></span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.15em]">Official Registry</span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6 md:p-10">
                <div class="max-w-7xl mx-auto">
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.pop-enter-active { transition: all 0.2s cubic-bezier(0.2, 1, 0.3, 1); }
.pop-leave-active { transition: all 0.15s ease-in; }
.pop-enter-from, .pop-leave-to { opacity: 0; transform: translateY(10px) scale(0.95); }
</style>
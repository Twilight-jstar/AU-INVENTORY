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
    ChevronUp,
    ShieldAlert
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

const isMobileMenuOpen = ref(false);
const showUserMenu = ref(false); 
const page = usePage();

const userName = computed(() => page.props.auth.user.name);
const userRole = computed(() => page.props.auth.user.role);

const navigationGroups = [
    {
        label: 'Analytics',
        items: [
            { name: 'Dashboard', href: route('dashboard'), icon: LayoutDashboard, active: 'dashboard', roles: ['Admin', 'Clerk', 'Custodian', 'Viewer'] },
        ]
    },
    {
        label: 'Inventory Control',
        items: [
            { name: 'Inventory Items', href: route('items.index'), icon: Package, active: 'items.*', roles: ['Admin', 'Clerk', 'Custodian', 'Viewer'] },
            { name: 'Asset Categories', href: route('categories.index'), icon: Tags, active: 'categories.*', roles: ['Admin', 'Clerk', 'Custodian'] },
            { name: 'Measurement Units', href: route('units.index'), icon: Scale, active: 'units.*', roles: ['Admin', 'Clerk', 'Custodian'] },
        ]
    },
    {
        label: 'Activity Logs',
        items: [
            { name: 'Stock In / Stock Out', href: route('transactions.index'), icon: HistoryIcon, active: 'transactions.*', roles: ['Admin', 'Clerk', 'Custodian', 'Viewer'] },
        ]
    }
];

const filteredGroups = computed(() => {
    return navigationGroups.map(group => ({
        ...group,
        items: group.items.filter(item => item.roles.includes(userRole.value as string))
    })).filter(group => group.items.length > 0);
});

const pageTitle = computed(() => {
    if (page.props.title) return page.props.title;
    for (const group of navigationGroups) {
        const activeNav = group.items.find(item => route().current(item.active as string));
        if (activeNav) return activeNav.name;
    }
    return 'Inventory Management';
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
        
        <aside class="hidden md:flex flex-col w-64 bg-purple-900 sticky top-0 h-screen z-20 shadow-2xl border-r border-purple-800">
            <div class="p-6">
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-3 text-white">
                        <div class="bg-white/10 p-2 rounded-lg">
                            <Package class="w-6 h-6 text-purple-300" />
                        </div>
                        <span class="font-bold tracking-tight text-xl uppercase">ALF Inventory</span>
                    </div>
                    <p class="text-[9px] text-purple-300/50 font-black uppercase tracking-[0.25em] mt-2">Management System v2.0</p>
                </div>
            </div>

            <nav class="flex-1 px-4 space-y-4 mt-2 overflow-y-auto no-scrollbar">
                <div v-for="group in filteredGroups" :key="group.label" class="space-y-1">
                    <h3 class="px-4 text-[9px] font-black text-purple-300/30 uppercase tracking-[0.2em] mb-1">
                        {{ group.label }}
                    </h3>
                    
                    <div class="space-y-0.5">
                        <Link 
                            v-for="item in group.items" 
                            :key="item.name"
                            :href="item.href"
                            class="flex items-center gap-3 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 group"
                            :class="route().current(item.active) 
                                ? 'bg-white/15 text-white shadow-inner border border-white/10' 
                                : 'text-purple-100/70 hover:bg-white/5 hover:text-white'"
                        >
                            <component :is="item.icon" class="w-4 h-4 opacity-70 group-hover:opacity-100 transition-opacity" />
                            {{ item.name }}
                        </Link>
                    </div>
                </div>
            </nav>

            <div v-if="userRole !== 'Viewer'" class="mx-4 mb-4 px-3 py-2 bg-white/5 border border-white/5 rounded-xl">
                <div class="flex items-center gap-1.5 mb-0.5">
                    <ShieldAlert class="w-2.5 h-2.5 text-purple-400/80" />
                    <span class="text-[8px] font-black text-purple-400/80 uppercase tracking-[0.15em]">System Live</span>
                </div>
                <p class="text-[9px] text-purple-100/40 leading-tight">Monitoring active logs.</p>
            </div>

            <div class="p-4 user-menu-container border-t border-purple-800 bg-purple-950/30">
                <div class="p-3 rounded-2xl flex items-center justify-between hover:bg-white/5 transition-colors cursor-pointer" @click.stop="showUserMenu = !showUserMenu">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="shrink-0 w-8 h-8 rounded-lg bg-purple-700 border border-purple-500 flex items-center justify-center text-purple-100 shadow-sm">
                            <User class="w-4 h-4" />
                        </div>
                        <div class="flex flex-col overflow-hidden">
                            <span class="text-xs text-white font-bold truncate">{{ userName }}</span>
                            <span class="text-[9px] text-purple-300/60 font-black uppercase tracking-widest">{{ userRole }}</span>
                        </div>
                    </div>
                    <ChevronUp class="w-4 h-4 text-purple-300 transition-transform duration-300" :class="{'rotate-180': showUserMenu}" />
                </div>

                <Transition name="pop">
                    <div v-if="showUserMenu" class="absolute bottom-24 left-4 right-4 bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden z-50">
                        <Link 
                            :href="route('logout')" 
                            method="post" 
                            as="button"
                            class="flex w-full items-center gap-3 px-5 py-4 text-xs font-bold text-red-600 hover:bg-red-50 transition-colors"
                        >
                            <LogOut class="w-4 h-4" />
                            SIGN OUT OF SYSTEM
                        </Link>
                    </div>
                </Transition>
            </div>
        </aside>

        <main class="flex-1 relative z-10 flex flex-col h-screen overflow-hidden">
            
            <div class="absolute inset-0 z-0 flex items-center justify-center pointer-events-none opacity-20 overflow-hidden">
                <img src="/images/bg.png" alt="ALF Logo Watermark" class="w-[100%] md:w-[80%] h-auto object-contain">
            </div>

            <header class="w-full bg-white/70 backdrop-blur-xl border-b border-slate-200 px-6 md:px-10 py-5">
                <div class="max-w-7xl mx-auto flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 tracking-tight leading-none uppercase">
                            {{ pageTitle }}
                        </h2>
                        <div class="flex items-center gap-2 mt-1.5">
                            <div class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Official Registry</span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6 md:p-10 no-scrollbar">
                <div class="max-w-7xl mx-auto">
                    <div class="relative z-10">
                        <slot />
                    </div>
                </div>
            </div>

        </main>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.pop-enter-active {
    transition: all 0.3s ease-out;
}
.pop-leave-active {
    transition: all 0.2s ease-in;
}
.pop-enter-from,
.pop-leave-to {
    opacity: 0;
    transform: translateY(10px) scale(0.95);
}
</style>
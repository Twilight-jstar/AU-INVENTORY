<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'; 
import { Link, usePage } from '@inertiajs/vue3'; 
import { 
    Package, 
    Tags, 
    Scale, 
    History as HistoryIcon, 
    LayoutDashboard,
    LogOut,
    User,
    ChevronUp,
    X,
    CircleCheck,
    CircleAlert,
    TriangleAlert
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

interface FlashProps {
    success?: string;
    error?: string;
    warning?: string;
}

const showUserMenu = ref(false); 
const page = usePage();

const flash = computed(() => page.props.flash as FlashProps);
const userName = computed(() => page.props.auth.user?.name || 'Guest User');
const userRole = computed(() => page.props.auth.user?.role || 'Viewer');

const showNotification = ref(false);
watch(() => page.props.flash as FlashProps, (newFlash) => {
    if (newFlash?.success || newFlash?.error || newFlash?.warning) {
        showNotification.value = true;
        setTimeout(() => showNotification.value = false, 5000);
    }
}, { deep: true });

const navigationGroups = [
    {
        label: 'Analytics',
        items: [
            { name: 'Dashboard', routeName: 'dashboard', icon: LayoutDashboard, active: 'dashboard', roles: ['Admin', 'Clerk', 'Custodian', 'Viewer'] },
        ]
    },
    {
        label: 'Inventory Control',
        items: [
            { name: 'Inventory Items', routeName: '/items', icon: Package, active: 'items.*', roles: ['Admin', 'Clerk', 'Custodian', 'Viewer'] },
            { name: 'Asset Categories', routeName: 'categories.index', icon: Tags, active: 'categories.*', roles: ['Admin', 'Clerk', 'Custodian'] },
            { name: 'Measurement Units', routeName: 'units.index', icon: Scale, active: 'units.*', roles: ['Admin', 'Clerk', 'Custodian'] },
        ]
    },
    {
        label: 'System Access',
        items: [
            { name: 'Manage Users', routeName: 'users.index', icon: User, active: 'users.*', roles: ['Admin'] },
        ]
    },
    {
        label: 'Activity Logs',
        items: [
            { name: 'Stock In / Stock Out', routeName: '/transactions', icon: HistoryIcon, active: 'transactions.*', roles: ['Admin', 'Clerk', 'Custodian', 'Viewer'] },
        ]
    }
];

const isRouteActive = (activePattern: string) => {
    try {
        return route().current(activePattern);
    } catch (e) {
        return false;
    }
};

const filteredGroups = computed(() => {
    return navigationGroups.map(group => ({
        ...group,
        items: group.items.filter(item => item.roles.includes(userRole.value as string))
    })).filter(group => group.items.length > 0);
});

const pageTitle = computed(() => {
    if (page.props.title) return page.props.title;
    for (const group of navigationGroups) {
        const activeNav = group.items.find(item => isRouteActive(item.active));
        if (activeNav) return activeNav.name;
    }
    return 'Inventory Management';
});

const closeUserMenu = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.user-menu-container')) showUserMenu.value = false;
};

onMounted(() => window.addEventListener('click', closeUserMenu));
onUnmounted(() => window.removeEventListener('click', closeUserMenu));
</script>

<template>
    <div class="min-h-screen flex flex-col md:flex-row relative bg-slate-50">
        <Transition name="fade-slide">
            <div v-if="showNotification" class="fixed top-6 right-6 z-[9999] max-w-md w-full flex flex-col gap-2 pointer-events-none">
                <div v-if="flash.success" class="pointer-events-auto bg-emerald-500 text-white rounded-xl p-4 shadow-2xl flex items-start gap-3">
                    <CircleCheck class="w-5 h-5 shrink-0 mt-0.5" />
                    <div class="flex-1 text-sm font-bold">{{ flash.success }}</div>
                    <button @click="showNotification = false"><X class="w-4 h-4" /></button>
                </div>
                <div v-if="flash.error" class="pointer-events-auto bg-rose-500 text-white rounded-xl p-4 shadow-2xl flex items-start gap-3">
                    <CircleAlert class="w-5 h-5 shrink-0 mt-0.5" />
                    <div class="flex-1 text-sm font-bold">{{ flash.error }}</div>
                    <button @click="showNotification = false"><X class="w-4 h-4" /></button>
                </div>
            </div>
        </Transition>

        <aside class="hidden md:flex flex-col w-64 bg-purple-900 sticky top-0 h-screen z-20 shadow-2xl border-r border-purple-800">
            <div class="p-6">
                <div class="flex items-center gap-3 text-white">
                    <div class="bg-white/10 p-2 rounded-lg"><Package class="w-6 h-6 text-purple-300" /></div>
                    <span class="font-bold tracking-tight text-xl uppercase">ALF Inventory</span>
                </div>
            </div>

            <nav class="flex-1 px-4 space-y-4 mt-2 overflow-y-auto no-scrollbar">
                <div v-for="group in filteredGroups" :key="group.label" class="space-y-1">
                    <h3 class="px-4 text-[9px] font-black text-purple-300/30 uppercase tracking-[0.2em] mb-1">{{ group.label }}</h3>
                    <div class="space-y-0.5">
                        <Link 
                            v-for="item in group.items" 
                            :key="item.name"
                            :href="item.routeName.startsWith('/') ? item.routeName : route(item.routeName)"
                            class="flex items-center gap-3 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 group"
                            :class="isRouteActive(item.active) ? 'bg-white/15 text-white shadow-inner border border-white/10' : 'text-purple-100/70 hover:bg-white/5 hover:text-white'"
                        >
                            <component :is="item.icon" class="w-4 h-4 opacity-70 group-hover:opacity-100" />
                            {{ item.name }}
                        </Link>
                    </div>
                </div>
            </nav>

            <div class="p-4 user-menu-container border-t border-purple-800 bg-purple-950/30">
                <div class="p-3 rounded-2xl flex items-center justify-between hover:bg-white/5 cursor-pointer" @click.stop="showUserMenu = !showUserMenu">
                    <div class="flex items-center gap-3">
                        <div class="shrink-0 w-8 h-8 rounded-lg bg-purple-700 flex items-center justify-center text-purple-100"><User class="w-4 h-4" /></div>
                        <div class="flex flex-col overflow-hidden">
                            <span class="text-xs text-white font-bold truncate">{{ userName }}</span>
                            <span class="text-[9px] text-purple-300/60 font-black uppercase tracking-widest">{{ userRole }}</span>
                        </div>
                    </div>
                    <ChevronUp class="w-4 h-4 text-purple-300 transition-transform" :class="{'rotate-180': showUserMenu}" />
                </div>
                <Transition name="pop">
                    <div v-if="showUserMenu" class="absolute bottom-24 left-4 right-4 bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden z-50">
                        <Link :href="route('logout')" method="post" as="button" class="flex w-full items-center gap-3 px-5 py-4 text-xs font-bold text-red-600 hover:bg-red-50">
                            <LogOut class="w-4 h-4" /> SIGN OUT
                        </Link>
                    </div>
                </Transition>
            </div>
        </aside>

        <main class="flex-1 flex flex-col h-screen overflow-hidden">
            <header class="w-full bg-white/70 backdrop-blur-xl border-b border-slate-200 px-6 py-5">
                <h2 class="text-lg font-bold text-slate-800 uppercase">{{ pageTitle }}</h2>
            </header>
            <div class="flex-1 overflow-y-auto p-6 md:p-10 no-scrollbar">
                <slot />
            </div>
        </main>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.pop-enter-active, .pop-leave-active { transition: all 0.2s; }
.pop-enter-from, .pop-leave-to { opacity: 0; transform: translateY(10px); }
</style>
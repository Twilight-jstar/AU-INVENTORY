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
    Menu,
    X,
    User,
    ChevronUp,
    ShieldAlert,
    CircleCheck,
    CircleAlert,
    TriangleAlert
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

const isMobileMenuOpen = ref(false);
const showUserMenu = ref(false); 
const page = usePage();

const userName = computed(() => page.props.auth.user?.name || 'Guest User');
const userRole = computed(() => page.props.auth.user?.role || 'Viewer');

// ============================================================
// FIX: PINALAKAS NA NOTIFICATION LOGIC
// ============================================================
const showNotification = ref(false);

watch(() => page.props.flash, (newFlash) => {
    // Para makita mo sa Console (F12) kung ano ang pinapadala ng Laravel
    console.log("System Flash Trace:", JSON.parse(JSON.stringify(newFlash)));

    // Checheck natin kung may laman kahit alin sa tatlo
    const hasFlash = newFlash && (newFlash.success || newFlash.error || newFlash.warning);

    if (hasFlash) {
        // Reset visibility para mag-trigger ang animation
        showNotification.value = false;
        
        setTimeout(() => {
            showNotification.value = true;
            
            // Mawawala after 5 seconds
            setTimeout(() => {
                showNotification.value = false;
            }, 5000);
        }, 100);
    }
}, { deep: true, immediate: true }); 
// ============================================================

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
        
        <Transition name="fade-slide">
            <div v-if="showNotification" class="fixed top-6 right-6 z-[9999] max-w-md w-full flex flex-col gap-2">
                
                <div v-if="$page.props.flash?.success" class="bg-emerald-500 text-white rounded-xl p-4 shadow-2xl flex items-start gap-3 border border-emerald-400">
                    <CircleCheck class="w-5 h-5 text-white shrink-0 mt-0.5" />
                    <div class="flex-1 text-sm font-bold">{{ $page.props.flash.success }}</div>
                    <button @click="showNotification = false" class="text-white/80 hover:text-white shrink-0"><X class="w-4 h-4" /></button>
                </div>

                <div v-if="$page.props.flash?.error" class="bg-rose-500 text-white rounded-xl p-4 shadow-2xl flex items-start gap-3 border border-rose-400">
                    <CircleAlert class="w-5 h-5 text-white shrink-0 mt-0.5" />
                    <div class="flex-1 text-sm font-bold">{{ $page.props.flash.error }}</div>
                    <button @click="showNotification = false" class="text-white/80 hover:text-white shrink-0"><X class="w-4 h-4" /></button>
                </div>

                <div v-if="$page.props.flash?.warning" class="bg-amber-500 text-white rounded-xl p-4 shadow-2xl flex items-start gap-3 border border-amber-400">
                    <TriangleAlert class="w-5 h-5 text-white shrink-0 mt-0.5" />
                    <div class="flex-1 text-sm font-bold">{{ $page.props.flash.warning }}</div>
                    <button @click="showNotification = false" class="text-white/80 hover:text-white shrink-0"><X class="w-4 h-4" /></button>
                </div>
            </div>
        </Transition>

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
            </div>
        </aside>

        <main class="flex-1 relative z-10 flex flex-col h-screen overflow-hidden">
            <header class="w-full bg-white/70 backdrop-blur-xl border-b border-slate-200 px-6 md:px-10 py-5">
                <div class="max-w-7xl mx-auto flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 tracking-tight leading-none uppercase">
                            {{ pageTitle }}
                        </h2>
                    </div>
                </div>
            </header>
            <div class="flex-1 overflow-y-auto p-6 md:p-10 no-scrollbar">
                <div class="max-w-7xl mx-auto">
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateY(-20px) translateX(20px); }
</style>
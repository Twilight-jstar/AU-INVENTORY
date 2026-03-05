<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { 
    Package, 
    Tags, 
    Scale, 
    History as HistoryIcon, 
    LayoutDashboard,
    LogOut,
    Menu,
    X 
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

// Note: If Ziggy is configured globally in app.ts, 
// you don't need to import { route } here.

const isMobileMenuOpen = ref(false);

const navItems = [
    { name: 'Dashboard', href: route('dashboard'), icon: LayoutDashboard, active: 'dashboard' },
    { name: 'Items', href: route('items.index'), icon: Package, active: 'items.*' },
    { name: 'Categories', href: route('categories.index'), icon: Tags, active: 'categories.*' },
    { name: 'Units', href: route('units.index'), icon: Scale, active: 'units.*' },
    { name: 'Transactions', href: route('transactions.index'), icon: HistoryIcon, active: 'transactions.*' },
];
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col md:flex-row">
        <aside class="hidden md:flex flex-col w-64 bg-purple-900 border-r border-purple-800 shadow-xl">
            <div class="p-6 flex items-center gap-2 font-bold text-xl text-white">
                <Package class="w-8 h-8 text-purple-300" />
                <span>ALF Inventory</span>
            </div>

            <nav class="flex-1 px-4 space-y-1">
                <Link 
                    v-for="item in navItems" 
                    :key="item.name"
                    :href="item.href"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200"
                    :class="route().current(item.active) 
                        ? 'bg-white/20 text-white shadow-sm' 
                        : 'text-purple-100 hover:bg-white/10 hover:text-white'"
                >
                    <component :is="item.icon" class="w-5 h-5" />
                    {{ item.name }}
                </Link>
            </nav>

            <div class="p-4 border-t border-purple-800">
                <Link 
                    :href="route('logout')" 
                    method="post" 
                    as="button"
                    type="button"
                    class="flex w-full items-center gap-3 px-3 py-2 text-sm font-medium text-white hover:bg-white/10 rounded-lg transition-all duration-200 text-left"
                >
                    <LogOut class="w-5 h-5 text-purple-300" />
                    Log Out
                </Link>
            </div>
        </aside>

        <header class="md:hidden bg-purple-900 border-b border-purple-800 p-4 flex items-center justify-between text-white">
            <div class="flex items-center gap-2 font-bold">
                <Package class="w-6 h-6 text-purple-300" />
                <span>ALF Inventory</span>
            </div>
            <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="p-2 text-white hover:bg-white/10 rounded-lg">
                <Menu v-if="!isMobileMenuOpen" class="w-6 h-6" />
                <X v-else class="w-6 h-6" />
            </button>
        </header>

        <div v-if="isMobileMenuOpen" class="md:hidden bg-white border-b border-gray-200 px-4 py-2 space-y-1 shadow-lg">
            <Link 
                v-for="item in navItems" 
                :key="item.name"
                :href="item.href"
                @click="isMobileMenuOpen = false"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-base font-medium transition-colors"
                :class="route().current(item.active) 
                    ? 'bg-purple-100 text-purple-900' 
                    : 'text-gray-600 hover:bg-gray-100'"
            >
                <component :is="item.icon" class="w-5 h-5" />
                {{ item.name }}
            </Link>
        </div>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto bg-cover bg-center bg-no-repeat bg-fixed]"
            style="background-image: url('/images/auslbg1.jpg');">
            <div class="max-w-7xl mx-auto">
                <slot />
            </div>
        </main>
    </div>
</template>

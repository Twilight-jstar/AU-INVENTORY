<script setup lang="ts">
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    Package, 
    Tags, 
    Scale, 
    History, 
    LayoutDashboard,
    LogOut,
    Menu,
    X 
} from 'lucide-vue-next';

const isMobileMenuOpen = ref(false);

const navItems = [
    { name: 'Dashboard', href: route('dashboard'), icon: LayoutDashboard, active: 'dashboard' },
    { name: 'Items', href: route('items.index'), icon: Package, active: 'items.*' },
    { name: 'Categories', href: route('categories.index'), icon: Tags, active: 'categories.*' },
    { name: 'Units', href: route('units.index'), icon: Scale, active: 'units.*' },
    { name: 'Transactions', href: route('transactions.index'), icon: History, active: 'transactions.*' },
];
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col md:flex-row">
        <aside class="hidden md:flex flex-col w-64 bg-purple-900 border-r border-gray-200">
            <div class="p-6 flex items-center gap-2 font-bold text-xl text-white">
                <Package class="w-8 h-8" />
                <span>Inventory</span>
            </div>

            <nav class="flex-1 px-4 space-y-1">
                <Link 
                    v-for="item in navItems" 
                    :key="item.name"
                    :href="item.href"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-white transition-colors"
                    :class="route().current(item.active) 
                        ? 'bg-white/10 text-white' 
                        : 'text-white hover:bg-gray-50 hover:text-gray-900'"
                >
                    <component :is="item.icon" class="w-5 h-5" />
                    {{ item.name }}
                </Link>
            </nav>

            <div class="p-4 border-t border-gray-200">
                <Link 
                    :href="route('logout')" 
                    method="post" 
                    as="button"
                    class="flex w-full items-center gap-3 px-3 py-2 text-sm font-medium text-white hover:bg-gray-50 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors"
                >
                    <LogOut class="w-5 h-5" />
                    Log Out
                </Link>
            </div>
        </aside>

        <header class="md:hidden bg-white border-b border-gray-200 p-4 flex items-center justify-between">
            <div class="flex items-center gap-2 font-bold text-primary">
                <Package class="w-6 h-6" />
                <span>Inventory</span>
            </div>
            <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="p-2 text-gray-600">
                <Menu v-if="!isMobileMenuOpen" class="w-6 h-6" />
                <X v-else class="w-6 h-6" />
            </button>
        </header>

        <div v-if="isMobileMenuOpen" class="md:hidden bg-white border-b border-gray-200 px-4 py-2 space-y-1">
            <Link 
                v-for="item in navItems" 
                :key="item.name"
                :href="item.href"
                @click="isMobileMenuOpen = false"
                class="block px-3 py-2 rounded-lg text-base font-medium"
                :class="route().current(item.active) ? 'bg-primary/10 text-primary' : 'text-gray-600'"
            >
                {{ item.name }}
            </Link>
        </div>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-7xl mx-auto">
                <slot />
            </div>
        </main>
    </div>
</template>
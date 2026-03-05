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
    <div class="min-h-screen flex flex-col md:flex-row relative">
        <div 
            class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat"
            style="background-image: url('/images/auslbg1.jpg');"
        >
            <div class="absolute inset-0 bg-black/5"></div>
        </div>

        <aside class="hidden md:flex flex-col w-64 bg-purple-900 border-r sticky top-0 h-screen border-purple-800 shadow-xl z-20">
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
<<<<<<< HEAD
                        ? 'bg-white/10 text-white' 
                        : 'text-white-600 hover:bg-white-50 hover:text-white-900'"
=======
                        ? 'bg-white/20 text-white shadow-sm' 
                        : 'text-purple-100 hover:bg-white/10 hover:text-white'"
>>>>>>> 8a52d88ef3371a8871a24cb700a76a0ed0de123b
                >
                    <component :is="item.icon" class="w-5 h-5" />
                    {{ item.name }}
                </Link>
            </nav>

<<<<<<< HEAD
            <div class="p-4 border-t border-white-200">
=======
            <div class="p-4 border-t border-purple-800">
>>>>>>> 8a52d88ef3371a8871a24cb700a76a0ed0de123b
                <Link 
                    :href="route('logout')" 
                    method="post" 
                    as="button"
<<<<<<< HEAD
                    class="flex w-full items-center gap-3 px-3 py-2 text-sm font-medium text-white-600 hover:bg-white-50 rounded-lg transition-colors"
=======
                    type="button"
                    class="flex w-full items-center gap-3 px-3 py-2 text-sm font-medium text-white hover:bg-white/10 rounded-lg transition-all duration-200 text-left"
>>>>>>> 8a52d88ef3371a8871a24cb700a76a0ed0de123b
                >
                    <LogOut class="w-5 h-5 text-purple-300" />
                    Log Out
                </Link>
            </div>
        </aside>

        <header class="md:hidden bg-purple-900 border-b border-purple-800 p-4 flex items-center justify-between text-white z-20">
            <div class="flex items-center gap-2 font-bold">
                <Package class="w-6 h-6 text-purple-300" />
                <span>ALF Inventory</span>
            </div>
            <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="p-2 text-white hover:bg-white/10 rounded-lg">
                <Menu v-if="!isMobileMenuOpen" class="w-6 h-6" />
                <X v-else class="w-6 h-6" />
            </button>
        </header>

        <div v-if="isMobileMenuOpen" class="md:hidden bg-white border-b border-gray-200 px-4 py-2 space-y-1 shadow-lg z-20">
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

        <main class="flex-1 p-4 md:p-8 relative z-10 overflow-y-auto">
            <div class="max-w-7xl mx-auto">
                <slot />
            </div>
        </main>
    </div>
</template>

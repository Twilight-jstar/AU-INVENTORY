<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    low_stock_items: Array,
    recent_transactions: Array
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-sm font-medium text-gray-500">Total Items</h3>
                    <p class="text-2xl font-bold">{{ stats.total_items }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-sm font-medium text-red-500">Low Stock</h3>
                    <p class="text-2xl font-bold text-red-600">{{ stats.low_stock_count }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-sm font-medium text-gray-500">Categories</h3>
                    <p class="text-2xl font-bold">{{ stats.total_categories }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-sm font-medium text-blue-500">Today's Transactions</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ stats.recent_updates }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="p-4 border-b font-bold text-gray-700">Critical Stock Levels</div>
                    <ul class="divide-y">
                        <li v-for="item in low_stock_items" :key="item.id" class="p-4 flex justify-between">
                            <span>{{ item.name }}</span>
                            <span class="font-bold text-red-600">{{ item.quantity }} {{ item.unit?.name }}</span>
                        </li>
                        <li v-if="low_stock_items.length === 0" class="p-4 text-center text-gray-400 italic">All stock levels healthy.</li>
                    </ul>
                </div>

                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="p-4 border-b font-bold text-gray-700">Recent Activity</div>
                    <ul class="divide-y">
                        <li v-for="trx in recent_transactions" :key="trx.id" class="p-4 flex justify-between items-center">
                            <div>
                                <div class="font-medium">{{ trx.item?.name }}</div>
                                <div class="text-xs text-gray-400">{{ new Date(trx.created_at).toLocaleTimeString() }}</div>
                            </div>
                            <span :class="trx.type === 'In' ? 'text-green-600' : 'text-red-600'" class="font-bold">
                                {{ trx.type === 'In' ? '+' : '-' }}{{ trx.quantity }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

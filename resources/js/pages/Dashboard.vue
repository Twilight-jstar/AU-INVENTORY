<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

// 1. IMPORT CHART COMPONENTS
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

// 2. REGISTER CHART
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    stats: Object,
    low_stock_items: Array,
    recent_transactions: Array,
    top_stock_items: Array 
});

// 3. SETUP CHART DATA
const chartData = computed(() => {
    const items = props.top_stock_items || []; 
    return {
        labels: items.map(item => item.name), 
        datasets: [{
            label: 'Stock Quantity',
            backgroundColor: '#6366f1', 
            borderRadius: 4,
            data: items.map(item => item.quantity) 
        }]
    }
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Inventory Overview</h2>
                <a :href="route('reports.download')" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md shadow-sm transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Generate Report
                </a>
            </div>

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

            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h3 class="text-sm font-bold text-gray-700 mb-4">Top 5 Highly Stocked Items</h3>
                <div class="h-[300px] w-full relative">
                    <Bar :data="chartData" :options="chartOptions" />
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-6 items-start">
                <div class="bg-white rounded-lg shadow-sm border w-full lg:w-1/2 flex flex-col h-[400px]">
                    <div class="p-4 border-b font-bold text-gray-700">Critical Stock Levels</div>
                    <ul class="divide-y overflow-y-auto flex-1 custom-scrollbar">
                        <li v-for="item in low_stock_items" :key="item.id" class="p-4 flex justify-between">
                            <span>{{ item.name }}</span>
                            <span class="font-bold text-red-600">{{ item.quantity }} {{ item.unit?.name }}</span>
                        </li>
                        <li v-if="low_stock_items.length === 0" class="p-4 text-center text-gray-400 italic">All stock levels healthy.</li>
                    </ul>
                </div>

                <div class="bg-white rounded-lg shadow-sm border w-full lg:w-1/2 flex flex-col h-[400px]">
                    <div class="p-4 border-b font-bold text-gray-700">Recent Activity</div>
                    <ul class="divide-y overflow-y-auto flex-1 custom-scrollbar">
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

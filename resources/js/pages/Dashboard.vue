<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3'; 
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { 
    Chart as ChartJS, 
    Title, Tooltip, Legend, 
    BarElement, CategoryScale, LinearScale 
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const page = usePage();
const userName = computed(() => page.props.auth.user.name);

const props = defineProps({
    stats: Object,
    low_stock_items: Array,
    recent_transactions: Array,
    top_stock_items: {
        type: Array,
        default: () => [] 
    }
});

const chartData = computed(() => {
    return {
        labels: props.top_stock_items.map(item => item.name), 
        datasets: [{
            label: 'Stock Quantity',
            backgroundColor: '#8b5cf6', 
            borderRadius: 6,
            data: props.top_stock_items.map(item => item.quantity) 
        }]
    }
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false 
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: { display: false }
        },
        x: {
            grid: { display: false }
        }
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Welcome back, {{ userName }}!
                    </h1>
                    <p class="text-sm text-gray-500">Here's what's happening with your inventory today.</p>
                </div>
                
                <a :href="route('items.index')" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md shadow-sm transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Generate Report
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Items</h3>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.total_items }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-sm font-medium text-red-500 uppercase tracking-wider">Low Stock</h3>
                    <p class="text-3xl font-bold text-red-600 mt-1">{{ stats.low_stock_count }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Categories</h3>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.total_categories }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-sm font-medium text-blue-500 uppercase tracking-wider">Today</h3>
                    <p class="text-3xl font-bold text-blue-600 mt-1">{{ stats.recent_updates }}</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-6">Top 5 Highly Stocked Items</h3>
                <div class="h-[300px] w-full">
                    <Bar :data="chartData" :options="chartOptions" />
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-sm border flex flex-col h-[400px]">
                    <div class="p-4 border-b font-bold text-gray-700 bg-gray-50/50">Critical Stock Levels</div>
                    <ul class="divide-y overflow-y-auto flex-1">
                        <li v-for="item in low_stock_items" :key="item.id" class="p-4 flex justify-between hover:bg-gray-50 transition-colors">
                            <span class="text-gray-700 font-medium">{{ item.name }}</span>
                            <span class="font-bold text-red-600 px-2 py-1 bg-red-50 rounded text-sm">
                                {{ item.quantity }} {{ item.unit?.name }}
                            </span>
                        </li>
                        <li v-if="low_stock_items.length === 0" class="p-8 text-center text-gray-400 italic">
                            All stock levels are healthy.
                        </li>
                    </ul>
                </div>

                <div class="bg-white rounded-lg shadow-sm border flex flex-col h-[400px]">
                    <div class="p-4 border-b font-bold text-gray-700 bg-gray-50/50">Recent Activity</div>
                    <ul class="divide-y overflow-y-auto flex-1">
                        <li v-for="trx in recent_transactions" :key="trx.id" class="p-4 flex justify-between items-center hover:bg-gray-50 transition-colors">
                            <div>
                                <div class="font-medium text-gray-800">{{ trx.item?.name }}</div>
                                <div class="text-xs text-gray-400">{{ new Date(trx.created_at).toLocaleTimeString() }}</div>
                            </div>
                            <span :class="trx.type === 'In' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'" class="font-bold px-3 py-1 rounded-full text-sm">
                                {{ trx.type === 'In' ? '+' : '-' }}{{ trx.quantity }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
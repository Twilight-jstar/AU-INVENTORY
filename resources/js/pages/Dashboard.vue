<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Card from '@/components/ui/card/Card.vue'; 
import { Head, Link, usePage } from '@inertiajs/vue3'; 
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { 
    FileText, 
    AlertCircle, 
    History, 
    Box, 
    LayoutGrid,
    TrendingUp
} from 'lucide-vue-next';
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
            label: 'Current Stock',
            backgroundColor: '#581c87', // Deeper purple for a grounded feel
            borderRadius: 2,
            data: props.top_stock_items.map(item => item.quantity) 
        }]
    }
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: { color: '#f1f5f9', drawBorder: false },
            ticks: { color: '#94a3b8', font: { size: 10, weight: 'bold' } }
        },
        x: {
            grid: { display: false },
            ticks: { color: '#64748b', font: { size: 10 } }
        }
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="space-y-8 max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 pb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Administrative Overview</h1>
                    <p class="text-sm text-slate-500 mt-1 italic">Welcome back, {{ userName }}. Here is the current inventory status.</p>
                </div>
                
                <a 
                    :href="route('reports.download')" 
                    class="inline-flex items-center px-4 py-2 bg-slate-900 hover:bg-purple-900 text-white text-[10px] font-bold rounded-sm shadow-sm transition-all uppercase tracking-[0.15em]"
                >
                    <FileText class="w-3.5 h-3.5 mr-2" />
                    Export Inventory Report
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <Card class="p-5 border-none shadow-none ring-1 ring-slate-200">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2 bg-slate-50 rounded-sm text-slate-600"><Box class="w-4 h-4" /></div>
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Items</h3>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">{{ stats.total_items }}</p>
                </Card>

                <Card class="p-5 border-none shadow-none ring-1 ring-slate-200">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2 bg-amber-50 rounded-sm text-amber-600"><AlertCircle class="w-4 h-4" /></div>
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Low Stock</h3>
                    </div>
                    <p class="text-2xl font-bold text-amber-600">{{ stats.low_stock_count }}</p>
                </Card>

                <Card class="p-5 border-none shadow-none ring-1 ring-slate-200">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2 bg-slate-50 rounded-sm text-slate-600"><LayoutGrid class="w-4 h-4" /></div>
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Categories</h3>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">{{ stats.total_categories }}</p>
                </Card>

                <Card class="p-5 border-none shadow-none ring-1 ring-slate-200">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2 bg-slate-50 rounded-sm text-slate-600"><History class="w-4 h-4" /></div>
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Updates Today</h3>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">{{ stats.recent_updates }}</p>
                </Card>
            </div>

            <Card class="p-6 border-none ring-1 ring-slate-200 shadow-none">
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2">
                            <TrendingUp class="w-4 h-4 text-purple-600" />
                            Stock Distribution
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-1 uppercase font-medium">Quantity per item nomenclature</p>
                    </div>
                </div>
                <div class="h-[280px] w-full">
                    <Bar :data="chartData" :options="chartOptions" />
                </div>
            </Card>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <Card class="p-0 border-none ring-1 ring-slate-200 shadow-none h-[400px] flex flex-col overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] flex items-center gap-2">
                            <div class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></div>
                            Attention Required
                        </h3>
                    </div>
                    <div class="overflow-y-auto flex-1">
                        <table class="w-full text-left border-collapse">
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="item in low_stock_items" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-slate-700 font-medium italic">{{ item.name }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded-sm">
                                            {{ item.quantity }} {{ item.unit?.name }} remaining
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

                <Card class="p-0 border-none ring-1 ring-slate-200 shadow-none h-[400px] flex flex-col overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">
                            Recent Activity
                        </h3>
                    </div>
                    <div class="overflow-y-auto flex-1">
                        <div v-for="trx in recent_transactions" :key="trx.id" 
                            class="px-6 py-4 flex justify-between items-center border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div :class="trx.type === 'In' ? 'text-emerald-500' : 'text-slate-300'" class="p-1">
                                    <History class="w-4 h-4" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800 uppercase tracking-tight">{{ trx.item?.name }}</p>
                                    <p class="text-[10px] text-slate-400 font-medium">
                                        Recorded at {{ new Date(trx.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                                    </p>
                                </div>
                            </div>
                            <span :class="trx.type === 'In' ? 'text-emerald-600' : 'text-slate-600'" class="font-mono text-sm font-bold">
                                {{ trx.type === 'In' ? '+' : '-' }}{{ trx.quantity }}
                            </span>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
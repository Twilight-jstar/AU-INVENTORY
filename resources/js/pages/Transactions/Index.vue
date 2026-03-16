<script setup>
import { ref, computed } from 'vue';
import { Link, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import { 
    History, Download, Eye, PackagePlus, PackageMinus, XCircle, User, Building2, Box, Calendar, Filter, ArrowUpDown
} from 'lucide-vue-next';

const props = defineProps({ 
    transactions: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] }
});

const activeTab = ref('all');
const filterDept = ref('');
const filterCategory = ref('');
const startDate = ref('');
const endDate = ref('');
const sortBy = ref('latest');
const isModalOpen = ref(false);
const selectedTransaction = ref(null);

const filteredTransactions = computed(() => {
    let result = [...props.transactions];
    if (activeTab.value === 'in') result = result.filter(t => t.type === 'In');
    else if (activeTab.value === 'out') result = result.filter(t => t.type === 'Out');
    if (filterDept.value) result = result.filter(t => t.department === filterDept.value);
    if (filterCategory.value) result = result.filter(t => t.item?.category_id == filterCategory.value);
    
    if (startDate.value && endDate.value) {
        const start = new Date(startDate.value).setHours(0,0,0,0);
        const end = new Date(endDate.value).setHours(23,59,59,999);
        result = result.filter(t => {
            const trxDate = new Date(t.created_at).getTime();
            return trxDate >= start && trxDate <= end;
        });
    }
    return result.sort((a, b) => {
        const dateA = new Date(a.created_at).getTime();
        const dateB = new Date(b.created_at).getTime();
        if (sortBy.value === 'latest') return dateB - dateA;
        if (sortBy.value === 'oldest') return dateA - dateB;
        if (sortBy.value === 'az') return (a.item?.name || '').localeCompare(b.item?.name || '');
        if (sortBy.value === 'za') return (b.item?.name || '').localeCompare(a.item?.name || '');
        return 0;
    });
});

const exportDailyInReport = () => {
    if (!startDate.value) return;
    window.open(route('web.transactions.export-daily-in', { date: startDate.value }), '_blank');
};

const exportDepartmentReport = () => {
    if (!filterDept.value) return;
    window.open(route('web.transactions.export-by-department', { department: filterDept.value }), '_blank');
};

const openViewModal = (trx) => { selectedTransaction.value = trx; isModalOpen.value = true; };
const closeViewModal = () => { isModalOpen.value = false; selectedTransaction.value = null; };
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head title="Transaction History" />
    <AuthenticatedLayout>
        <div class="max-w-[1600px] mx-auto p-4 space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-slate-900 rounded-xl text-white shadow-lg shadow-slate-200"><History class="w-5 h-5" /></div>
                    <div>
                        <h1 class="text-lg font-black text-slate-900 leading-none uppercase tracking-tight">Inventory Logs</h1>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Movement Tracking</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button v-if="activeTab === 'in' && startDate" @click="exportDailyInReport" class="px-4 py-2 bg-emerald-600 text-white text-[10px] font-black rounded-lg hover:bg-emerald-700 uppercase flex items-center gap-2 transition-all shadow-md"><Download class="w-3.5 h-3.5" /> Daily In</button>
                    <button v-if="filterDept" @click="exportDepartmentReport" class="px-4 py-2 bg-blue-600 text-white text-[10px] font-black rounded-lg hover:bg-blue-700 uppercase flex items-center gap-2 transition-all shadow-md"><Download class="w-3.5 h-3.5" /> Dept Report</button>
                    <Link :href="route('web.transactions.stock-in')" class="bg-emerald-600 text-white px-4 py-2 text-[10px] font-black rounded-lg uppercase flex items-center gap-2 hover:bg-emerald-700 shadow-md shadow-emerald-100"><PackagePlus class="w-3.5 h-3.5" /> Stock In</Link>
                    <Link :href="route('web.transactions.stock-out')" class="bg-slate-900 text-white px-4 py-2 text-[10px] font-black rounded-lg uppercase flex items-center gap-2 hover:bg-slate-800 shadow-md shadow-slate-200"><PackageMinus class="w-3.5 h-3.5" /> Stock Out</Link>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-6 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <div class="bg-white p-1 rounded-lg border border-slate-200 flex h-9 shadow-sm">
                    <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-slate-900 text-white' : 'text-slate-400 hover:bg-slate-50'" class="flex-1 text-[9px] font-black uppercase rounded-md transition-all">All</button>
                    <button @click="activeTab = 'in'" :class="activeTab === 'in' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:bg-slate-50'" class="flex-1 text-[9px] font-black uppercase rounded-md transition-all">In</button>
                    <button @click="activeTab = 'out'" :class="activeTab === 'out' ? 'bg-purple-600 text-white' : 'text-slate-400 hover:bg-slate-50'" class="flex-1 text-[9px] font-black uppercase rounded-md transition-all">Out</button>
                </div>
                <div class="relative"><Building2 class="absolute left-3 top-2.5 w-3.5 h-3.5 text-slate-400" /><select v-model="filterDept" class="w-full h-9 pl-9 pr-3 bg-white border-slate-200 rounded-lg text-[10px] font-bold uppercase focus:ring-slate-900"><option value="">All Dept</option><option v-for="dept in departments" :key="dept.id" :value="dept.name">{{ dept.name }}</option></select></div>
                <div class="relative"><Filter class="absolute left-3 top-2.5 w-3.5 h-3.5 text-slate-400" /><select v-model="filterCategory" class="w-full h-9 pl-9 pr-3 bg-white border-slate-200 rounded-lg text-[10px] font-bold uppercase focus:ring-slate-900"><option value="">All Categories</option><option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option></select></div>
                <div class="relative"><ArrowUpDown class="absolute left-3 top-2.5 w-3.5 h-3.5 text-slate-400" /><select v-model="sortBy" class="w-full h-9 pl-9 pr-3 bg-white border-slate-200 rounded-lg text-[10px] font-bold uppercase focus:ring-slate-900"><option value="latest">Latest</option><option value="oldest">Oldest</option><option value="az">Item A-Z</option></select></div>
                <div class="md:col-span-2 flex gap-2 items-center">
                    <div class="flex-1 relative"><Calendar class="absolute left-2.5 top-2.5 w-3 h-3 text-slate-400" /><input type="date" v-model="startDate" class="w-full h-9 pl-8 pr-2 bg-white border-slate-200 rounded-lg text-[10px] font-bold"></div>
                    <div class="flex-1 relative"><Calendar class="absolute left-2.5 top-2.5 w-3 h-3 text-slate-400" /><input type="date" v-model="endDate" class="w-full h-9 pl-8 pr-2 bg-white border-slate-200 rounded-lg text-[10px] font-bold"></div>
                </div>
            </div>

            <Card class="border-none ring-1 ring-slate-200 shadow-xl bg-white rounded-2xl overflow-hidden">
                <div class="overflow-x-auto"> 
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-[10px] font-black uppercase">
                                <th class="py-3 px-3 border-b border-slate-100">Reference / Date</th>
                                <th class="py-3 px-2 border-b border-slate-100">Item Name</th> 
                                <th class="py-3 px-2 border-b border-slate-100">Department</th>
                                <th class="py-3 px-2 border-b border-slate-100">Personnel</th>
                                <th class="py-3 px-2 text-center border-b border-slate-100">Qty</th>
                                <th class="py-3 px-4 text-right border-b border-slate-100">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="trx in filteredTransactions" :key="trx.raw_id" class="hover:bg-slate-50/50 transition-colors group">
                                <td class="py-3 px-3">
                                    <div class="flex flex-col">
                                        <span class="text-[11px] font-black text-slate-700">{{ trx.id }}</span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase">{{ formatDate(trx.created_at) }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-2">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-900 uppercase text-[11px]">{{ trx.item?.name }}</span>
                                        <span class="text-[9px] text-slate-400">{{ trx.item?.category?.name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-2">
                                    <span :class="trx.type === 'In' ? 'text-emerald-600 bg-emerald-50' : 'text-purple-600 bg-purple-50'" class="text-[9px] font-black uppercase px-2 py-0.5 rounded border border-transparent">
                                        {{ trx.department || 'STOCK-IN' }}
                                    </span>
                                </td>
                                <td class="py-3 px-2">
                                    <span class="text-[10px] text-slate-700 font-bold uppercase">{{ trx.received_by || trx.released_to }}</span>
                                </td>
                                <td class="py-3 px-2 text-center">
                                    <span :class="trx.type === 'In' ? 'text-emerald-600' : 'text-purple-600'" class="font-black text-[11px]">{{ trx.type === 'In' ? '+' : '-' }}{{ trx.quantity }}</span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="openViewModal(trx)" class="p-1.5 text-slate-400 hover:text-blue-600"><Eye class="w-4 h-4" /></button>
                                        <a :href="route('web.transactions.export-pdf', trx.raw_id)" target="_blank" class="p-1.5 text-slate-400 hover:text-red-600"><Download class="w-4 h-4" /></a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
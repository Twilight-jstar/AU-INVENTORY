<script setup>
import { Link, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import { History, Plus, ArrowUpRight, ArrowDownLeft, Clock } from 'lucide-vue-next';

defineProps({ 
    transactions: Array 
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Activity Log" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <div class="flex justify-between items-end border-b border-slate-200 pb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Activity Log</h1>
                    <p class="text-sm text-slate-500 italic mt-1">History of all stock movements and adjustments.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Link 
                        :href="route('transactions.create')" 
                        class="bg-slate-900 hover:bg-purple-900 text-white px-4 py-2 text-xs font-bold rounded-sm shadow-sm transition-all uppercase tracking-widest flex items-center gap-2"
                    >
                        <Plus class="w-3.5 h-3.5" />
                        Record Movement
                    </Link>
                </div>
            </div>

            <Card class="p-0 border-none ring-1 ring-slate-200 shadow-none overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 text-[11px] font-bold uppercase tracking-[0.1em] border-b border-slate-200">
                                <th class="py-4 px-6 flex items-center gap-2">
                                    <Clock class="w-3 h-3" />
                                    Date & Time
                                </th>
                                <th class="py-4 px-6">Item Description</th>
                                <th class="py-4 px-6">Type</th>
                                <th class="py-4 px-6 text-center">Amount</th>
                                <th class="py-4 px-6">Source/Destination Details</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-700 text-sm divide-y divide-slate-100">
                            <tr v-for="trx in transactions" :key="trx.id" class="hover:bg-slate-50/50 transition-colors group">
                                <td class="py-4 px-6 text-slate-500 text-xs font-medium">
                                    {{ formatDate(trx.created_at) }}
                                </td>

                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-900 uppercase tracking-tight">
                                        {{ trx.item?.name || 'Unknown Item' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono tracking-tighter">
                                        {{ trx.item?.product_code || 'N/A' }}
                                    </div>
                                </td>

                                <td class="py-4 px-6">
                                    <div 
                                        class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full"
                                        :class="trx.type === 'In' 
                                            ? 'text-emerald-700 bg-emerald-50 border border-emerald-100' 
                                            : 'text-slate-600 bg-slate-50 border border-slate-200'"
                                    >
                                        <component :is="trx.type === 'In' ? ArrowUpRight : ArrowDownLeft" class="w-3 h-3" />
                                        {{ trx.type === 'In' ? 'Stock In' : 'Stock Out' }}
                                    </div>
                                </td>

                                <td class="py-4 px-6 text-center font-mono">
                                    <span 
                                        class="px-2 py-0.5 rounded-sm border font-bold"
                                        :class="trx.type === 'In' 
                                            ? 'bg-emerald-50 border-emerald-100 text-emerald-700' 
                                            : 'bg-slate-50 border-slate-200 text-slate-700'"
                                    >
                                        {{ trx.type === 'In' ? '+' : '-' }}{{ trx.quantity }}
                                    </span>
                                </td>

                                <td class="py-4 px-6 text-slate-500 italic text-xs leading-relaxed">
                                    {{ trx.note || 'Standard Adjustment' }}
                                </td>
                            </tr>
                            
                            <tr v-if="transactions.length === 0">
                                <td colspan="5" class="py-20 text-center text-slate-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <History class="w-10 h-10 text-slate-100" />
                                        <div class="space-y-1">
                                            <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Log is Empty</p>
                                            <p class="text-[11px] italic">No activity has been recorded yet.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>

            <div class="flex items-center gap-2 text-[10px] text-slate-400 uppercase font-bold tracking-widest">
                <div class="w-1 h-1 bg-emerald-400 rounded-full animate-pulse"></div>
                System Active • Verified Records
            </div>
        </div>
    </AuthenticatedLayout>
</template>
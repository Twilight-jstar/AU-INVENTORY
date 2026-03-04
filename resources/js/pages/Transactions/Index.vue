<script setup>
import { Link, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

defineProps({ transactions: Array });

// Helper to format date cleanly
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
    <Head title="Transaction History" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Transaction History</h1>
                <Link 
                    :href="route('transactions.create')" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow transition-colors flex items-center gap-2"
                >
                    <span class="font-bold">+</span> Record Movement
                </Link>
            </div>

            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr class="text-xs uppercase text-gray-500 font-semibold tracking-wider">
                                <th class="p-4">Timestamp</th>
                                <th class="p-4">Item Name</th>
                                <th class="p-4">Type</th>
                                <th class="p-4">Quantity</th>
                                <th class="p-4">Note</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-700">
                            <tr v-for="trx in transactions" :key="trx.id" class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                <td class="p-4 text-gray-500">
                                    {{ formatDate(trx.created_at) }}
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-gray-900">{{ trx.item?.name || 'Deleted Item' }}</div>
                                    <div class="text-xs text-gray-400 font-mono">{{ trx.item?.product_code }}</div>
                                </td>
                                <td class="p-4">
                                    <span 
                                        class="px-2 py-1 rounded-full text-xs font-bold"
                                        :class="trx.type === 'In' 
                                            ? 'bg-green-100 text-green-700' 
                                            : 'bg-red-100 text-red-700'"
                                    >
                                        {{ trx.type === 'In' ? 'STOCK IN' : 'STOCK OUT' }}
                                    </span>
                                </td>
                                <td class="p-4 font-mono font-medium">
                                    <span :class="trx.type === 'In' ? 'text-green-600' : 'text-red-600'">
                                        {{ trx.type === 'In' ? '+' : '-' }}{{ trx.quantity }}
                                    </span>
                                </td>
                                <td class="p-4 text-gray-400 italic">
                                    {{ trx.note || '—' }}
                                </td>
                            </tr>
                            
                            <tr v-if="transactions.length === 0">
                                <td colspan="5" class="p-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center gap-2">
                                        <History class="w-8 h-8 opacity-20" />
                                        <span>No transactions recorded yet.</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { Link, Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import { Plus, Package, AlertTriangle, FileText, Pencil, Trash2 } from 'lucide-vue-next';

defineProps({
    items: Array
});

// Delete functionality
const form = useForm({});
const deleteItem = (id) => {
    if (confirm('Are you sure you want to remove this item from the registry?')) {
        form.delete(route('items.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: add a toast notification here
            }
        });
    }
};

// Helper function to handle numeric comparison safely
const isLowStock = (item) => {
    const qty = Number(item.quantity);
    const min = Number(item.min_stock);
    return qty <= min;
};
</script>

<template>
    <Head title="Inventory Registry" />

    <AuthenticatedLayout>
        <div class="space-y-8 max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 pb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Inventory Registry</h1>
                    <p class="text-sm text-slate-500 mt-1 italic">Centralized database of school assets and supply levels.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Link 
                        v-if="$page.props.auth.user.role !== 'viewer'"
                        :href="route('items.create')" 
                        class="inline-flex items-center px-4 py-2 bg-slate-900 hover:bg-purple-900 text-white text-xs font-bold rounded-sm shadow-sm transition-all uppercase tracking-widest"
                    >
                        <Plus class="w-3.5 h-3.5 mr-2" />
                        Enroll New Item
                    </Link>
                </div>
            </div>

            <Card class="p-0 border-none ring-1 ring-slate-200 shadow-none overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 text-[11px] font-bold uppercase tracking-[0.1em] border-b border-slate-200">
                                <th class="py-4 px-6">Product Code</th>
                                <th class="py-4 px-6">Item Description</th>
                                <th class="py-4 px-6">Classification</th>
                                <th class="py-4 px-6 text-center">Current Stock</th>
                                <th class="py-4 px-6">Unit</th>
                                <th v-if="$page.props.auth.user.role !== 'viewer'" class="py-4 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-700 text-sm divide-y divide-slate-100">
                            <tr v-for="item in items" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6 font-mono text-[11px] text-slate-500 uppercase tracking-tighter">
                                    {{ item.product_code }}
                                </td>
                                <td class="py-4 px-6 font-bold text-slate-900">
                                    {{ item.name }}
                                </td>
                                <td class="py-4 px-6">
                                    <span v-if="item.category" class="text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-100 px-2 py-0.5 rounded-sm uppercase tracking-wide">
                                        {{ item.category.name }}
                                    </span>
                                    <span v-else class="text-slate-400 italic">Unassigned</span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center justify-center gap-2">
                                        <span 
                                            :class="isLowStock(item) ? 'text-red-700 bg-red-50 border-red-100' : 'text-slate-700 bg-slate-50 border-slate-200'"
                                            class="font-mono font-bold px-2 py-0.5 border rounded-sm"
                                        >
                                            {{ item.quantity }}
                                        </span>
                                        <AlertTriangle v-if="isLowStock(item)" class="w-3.5 h-3.5 text-red-600" />
                                    </div>
                                    <div class="text-center text-[9px] text-slate-400 mt-1" v-if="isLowStock(item)">
                                        Threshold: {{ item.min_stock }}
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-slate-500 font-medium italic text-xs">
                                    {{ item.unit?.name || 'unit' }}
                                </td>
                                
                                <td v-if="$page.props.auth.user.role !== 'viewer'" class="py-4 px-6 text-right">
                                    <div class="flex justify-end gap-3">
                                        <Link :href="route('items.edit', item.id)" class="text-slate-400 hover:text-purple-600 transition-colors">
                                            <Pencil class="w-4 h-4" />
                                        </Link>
                                        
                                        <button 
                                            v-if="['admin', 'custodian'].includes($page.props.auth.user.role)"
                                            @click="deleteItem(item.id)" 
                                            class="text-slate-400 hover:text-red-600 transition-colors"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="items.length === 0">
                                <td colspan="6" class="py-20 text-center text-slate-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <FileText class="w-10 h-10 text-slate-200" />
                                        <div class="space-y-1">
                                            <p class="font-bold text-slate-500 uppercase text-xs tracking-widest">No Records Found</p>
                                            <p class="text-xs italic">The current inventory registry is empty.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>

            <div class="flex items-center gap-2 text-[10px] text-slate-400 uppercase font-bold tracking-widest">
                <div class="w-1 h-1 bg-slate-300 rounded-full"></div>
                <span v-if="$page.props.auth.user.role === 'viewer'">Read-Only Audit View</span>
                <span v-else>Authorized Registry Management: {{ $page.props.auth.user.role }}</span>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
item.id
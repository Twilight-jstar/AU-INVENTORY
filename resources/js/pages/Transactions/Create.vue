<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import { ArrowLeft, Save, Loader2, AlertCircle } from 'lucide-vue-next';

const props = defineProps({ items: Array });

const form = useForm({
    item_id: '',
    type: 'In', 
    quantity: 1,
    // Stock In specific fields
    reference_no: '',
    received_by: '',
    date_received: new Date().toISOString().substr(0, 10),
    unit_cost: 0,
    // Stock Out specific fields
    released_to: '',
    released_by: '',
    department: '',
    purpose: '',
    date_released: new Date().toISOString().substr(0, 10),
});

// Helper to find the current stock of the selected item
const selectedItem = computed(() => {
    return props.items.find(i => i.id === form.item_id);
});

const isInsufficient = computed(() => {
    if (form.type === 'Out' && selectedItem.value) {
        return form.quantity > selectedItem.value.quantity;
    }
    return false;
});

const submit = () => form.post(route('transactions.store'));
</script>

<template>
    <Head title="Inventory Update" />

    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-200">
                <div class="flex items-center gap-4">
                    <Link :href="route('transactions.index')" class="group flex items-center text-slate-500 hover:text-purple-700 transition-colors text-sm font-medium">
                        <ArrowLeft class="w-4 h-4 mr-1 group-hover:-translate-x-1 transition-transform" />
                        Back to Activity
                    </Link>
                </div>
                <div class="text-right">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Update Stock Levels</h1>
                    <p class="text-xs text-slate-500 font-medium italic">Record movements to the inventory logs.</p>
                </div>
            </div>

            <Card class="p-0 border-none ring-1 ring-slate-200 shadow-none overflow-hidden bg-white">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Adjustment Type</span>
                    <div class="flex gap-2">
                        <button type="button" @click="form.type = 'In'" :class="form.type === 'In' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-400 border-slate-200'" class="px-4 py-1.5 text-[10px] font-bold uppercase border rounded-sm transition-all">Stock In</button>
                        <button type="button" @click="form.type = 'Out'" :class="form.type === 'Out' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-400 border-slate-200'" class="px-4 py-1.5 text-[10px] font-bold uppercase border rounded-sm transition-all">Stock Out</button>
                    </div>
                </div>

                <form @submit.prevent="submit" class="p-8 space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Select Item</label>
                        <select v-model="form.item_id" class="w-full border-slate-300 rounded-sm px-3 py-2.5 text-sm focus:ring-purple-600 outline-none" required>
                            <option value="" disabled>Choose an item from inventory...</option>
                            <option v-for="item in items" :key="item.id" :value="item.id">
                                {{ item.name }} — (Available: {{ item.quantity }})
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Quantity to Move</label>
                            <input v-model="form.quantity" type="number" step="0.1" min="0.1" class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm" :class="{'border-red-500 ring-1 ring-red-100': isInsufficient}" required />
                            <p v-if="isInsufficient" class="mt-1 text-[10px] text-red-500 font-bold flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" /> Cannot release more than available stock.
                            </p>
                        </div>
                        
                        <div v-if="form.type === 'In'">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Unit Cost</label>
                            <input v-model="form.unit_cost" type="number" step="0.01" class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm" />
                        </div>
                    </div>

                    <div v-if="form.type === 'In'" class="space-y-6 animate-in fade-in slide-in-from-top-2 duration-300">
                        <div class="grid grid-cols-2 gap-6 p-4 bg-emerald-50/50 rounded-sm border border-emerald-100">
                            <div>
                                <label class="block text-[10px] font-bold text-emerald-800 uppercase mb-2">Reference No</label>
                                <input v-model="form.reference_no" type="text" class="w-full border-emerald-200 rounded-sm px-3 py-2 text-sm" placeholder="Invoice / Receipt #" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-emerald-800 uppercase mb-2">Date Received</label>
                                <input v-model="form.date_received" type="date" class="w-full border-emerald-200 rounded-sm px-3 py-2 text-sm" required />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Received By (Staff Name)</label>
                            <input v-model="form.received_by" type="text" class="w-full border-slate-300 rounded-sm px-3 py-2.5 text-sm" placeholder="Who processed this delivery?" required />
                        </div>
                    </div>

                    <div v-if="form.type === 'Out'" class="space-y-6 animate-in fade-in slide-in-from-top-2 duration-300">
                        <div class="grid grid-cols-2 gap-6 p-4 bg-slate-50 rounded-sm border border-slate-200">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-800 uppercase mb-2">Released To</label>
                                <input v-model="form.released_to" type="text" class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm" placeholder="Receiver Name" required />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-800 uppercase mb-2">Department</label>
                                <input v-model="form.department" type="text" class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm" placeholder="Office / Dept" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Released By (Authorized)</label>
                                <input v-model="form.released_by" type="text" class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm" placeholder="Staff Name" required />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Date Released</label>
                                <input v-model="form.date_released" type="date" class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm" required />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Purpose</label>
                            <textarea v-model="form.purpose" rows="2" class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm" placeholder="Reason for release..."></textarea>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button 
                            type="submit" 
                            :disabled="form.processing || isInsufficient" 
                            class="bg-purple-900 hover:bg-slate-900 text-white px-8 py-2.5 text-xs font-bold rounded-sm flex items-center gap-2 disabled:opacity-50 transition-all uppercase tracking-widest shadow-md active:scale-95"
                        >
                            <component :is="form.processing ? Loader2 : Save" class="w-4 h-4" :class="{'animate-spin': form.processing}" />
                            {{ form.processing ? 'Saving...' : 'Confirm Update' }}
                        </button>
                    </div>
                </form>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
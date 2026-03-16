<script setup>
import { useForm, Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import { ArrowLeft, Plus, Trash2, Truck, Download, Save, RefreshCw } from 'lucide-vue-next';

const props = defineProps({ items: Array });
const page = usePage();
const recentlySubmitted = ref(false);
const submittedDate = ref('');

const form = useForm({
    supplier_name: '',
    received_by: page.props.auth.user.name, 
    date_received: new Date().toISOString().substr(0, 10),
    line_items: [{ item_id: '', quantity: 1, unit_cost: 0 }]
});

const generatedRefNo = computed(() => form.date_received);
const addItemRow = () => form.line_items.push({ item_id: '', quantity: 1, unit_cost: 0 });
const removeItemRow = (index) => form.line_items.length > 1 && form.line_items.splice(index, 1);

const triggerExport = () => {
    window.open(route('web.transactions.export-daily-in', { date: submittedDate.value }), '_blank');
};

const resetForm = () => {
    form.reset();
    recentlySubmitted.value = false;
};

const submit = () => {
    form.post(route('web.transactions.store-in'), {
        onBefore: () => { submittedDate.value = form.date_received; },
        onSuccess: () => { recentlySubmitted.value = true; },
    });
};
</script>

<template>
    <Head title="STOCK IN" />
    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto space-y-8 p-2 py-8">
            <div class="flex items-center gap-4 border-b border-slate-200 pb-6">
                <Link :href="route('web.transactions.index')" class="p-2 bg-white ring-1 ring-slate-200 rounded-sm hover:bg-slate-50 text-slate-400 transition-all"><ArrowLeft class="w-4 h-4" /></Link>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight uppercase">STOCK IN</h1>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.2em] mt-1">Reference Date: {{ generatedRefNo }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card class="p-2 border-none ring-1 ring-slate-200 shadow-none overflow-hidden bg-white">
                    <div class="px-3 py-2 border-b border-slate-100 bg-slate-50/50 flex items-center gap-2 mb-4">
                        <Truck class="w-3.5 h-3.5 text-slate-400" />
                        <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Inbound Log Details</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-3">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Supplier Name</label>
                            <input v-model="form.supplier_name" type="text" class="w-full border-none ring-1 ring-slate-200 rounded-sm text-sm h-10 px-3 font-semibold focus:ring-slate-900 transition-all" required :disabled="recentlySubmitted" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Date Received</label>
                            <input v-model="form.date_received" type="date" class="w-full border-none ring-1 ring-slate-200 rounded-sm text-sm h-10 px-3 font-semibold focus:ring-slate-900 transition-all" required :disabled="recentlySubmitted" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Received By</label>
                            <input v-model="form.received_by" type="text" class="w-full border-none ring-1 ring-slate-200 rounded-sm text-sm h-10 px-3 uppercase font-semibold bg-slate-50/50" readonly />
                        </div>
                    </div>
                </Card>

                <Card class="p-2 border-none ring-1 ring-slate-200 shadow-none overflow-hidden bg-white">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Property Description</th>
                                <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-500 w-32 border-l border-slate-200 text-center">Qty</th>
                                <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-500 w-40 border-l border-slate-200 text-right">Unit Cost</th>
                                <th class="px-4 py-3 w-12 text-center text-slate-300">#</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <tr v-for="(line, index) in form.line_items" :key="index">
                                <td class="px-4 py-3">
                                    <select v-model="line.item_id" class="w-full border-none ring-1 ring-slate-100 rounded-sm text-sm h-9 uppercase focus:ring-slate-900" required :disabled="recentlySubmitted">
                                        <option value="" disabled>Select Item...</option>
                                        <option v-for="item in items" :key="item.id" :value="item.id">{{ item.name }}</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3 border-l border-slate-50"><input v-model="line.quantity" type="number" step="0.1" class="w-full border-none text-center h-9 ring-1 ring-slate-100 rounded-sm focus:ring-slate-900" required :disabled="recentlySubmitted" /></td>
                                <td class="px-4 py-3 border-l border-slate-50"><input v-model="line.unit_cost" type="number" step="0.01" class="w-full border-none text-left h-9 ring-1 ring-slate-100 rounded-sm focus:ring-slate-900" :disabled="recentlySubmitted" /></td>
                                <td class="px-2 py-3 text-center"><button v-if="!recentlySubmitted" @click="removeItemRow(index)" type="button" class="text-slate-300 hover:text-red-500 transition-colors"><Trash2 class="w-4 h-4" /></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="!recentlySubmitted" class="p-3 bg-slate-50 border-t border-slate-100 flex justify-center mt-2">
                        <button @click="addItemRow" type="button" class="text-[10px] font-bold text-slate-900 uppercase tracking-[0.2em] flex items-center gap-2 hover:text-indigo-900 transition-all"><Plus class="w-3 h-3" /> Add Item Row</button>
                    </div>
                </Card>

                <div class="flex justify-end pt-4">
                    <div v-if="recentlySubmitted" class="flex gap-3">
                        <button @click="triggerExport" type="button" class="inline-flex items-center px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-bold rounded-sm uppercase tracking-[0.2em]"><Download class="w-4 h-4 mr-3" /> Download Inbound PDF</button>
                        <button @click="resetForm" type="button" class="inline-flex items-center px-8 py-4 bg-white ring-1 ring-slate-200 text-slate-600 text-[10px] font-bold rounded-sm hover:bg-slate-50 uppercase tracking-[0.2em]"><RefreshCw class="w-4 h-4 mr-3" /> New Entry</button>
                    </div>
                    <button v-else type="submit" :disabled="form.processing" class="inline-flex items-center px-10 py-4 bg-slate-900 hover:bg-black text-white text-[10px] font-bold rounded-sm uppercase tracking-[0.2em] disabled:opacity-50"><Save class="w-4 h-4 mr-3 text-indigo-400" /> {{ form.processing ? 'Saving...' : 'Save Inbound Entry' }}</button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
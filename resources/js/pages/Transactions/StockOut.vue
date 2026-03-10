<script setup>
import { useForm, Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import { 
    ArrowLeft, Save, Loader2, AlertCircle, Plus, Trash2, 
    Hash, Building2, UserCircle, FileText, Calendar, 
    AlertTriangle // BAGO: In-import natin ito para sa warning icon
} from 'lucide-vue-next';

const props = defineProps({ 
    items: Array, 
    departments: Array 
});

const page = usePage();
const recentlySubmitted = ref(false);
const submittedRef = ref('');

const form = useForm({
    department: '',
    released_to: '',
    released_by: page.props.auth.user?.name || 'Authorized Personnel', 
    department: '',
    purpose: '',
    date_released: new Date().toISOString().substr(0, 10),
    line_items: [{ item_id: '', quantity: 1 }]
});

const generatedRefNo = computed(() => form.date_released);

const addItemRow = () => form.line_items.push({ item_id: '', quantity: 1 });
const removeItemRow = (index) => form.line_items.length > 1 && form.line_items.splice(index, 1);

// Updated to export the specific batch by the date/ref_no
const triggerExport = () => {
    const url = route('transactions.export-by-department', { 
        department: form.department,
        date: submittedRef.value 
    });
    window.open(url, '_blank');
};

const resetForm = () => {
    form.reset();
    recentlySubmitted.value = false;
    submittedRef.value = '';
};

const submit = () => {
    form.post(route('transactions.store_bulk_out'), {
        onBefore: () => { 
            submittedRef.value = form.date_released; 
        },
        onSuccess: () => { 
            recentlySubmitted.value = true; 
        },
    });
};
</script>

<template>
    <Head title="STOCK OUT" />
    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto space-y-8 p-2 py-8">
            <div class="flex items-center gap-4 border-b border-slate-200 pb-6">
                <Link :href="route('transactions.index')" class="p-2 bg-white ring-1 ring-slate-200 rounded-sm hover:bg-slate-50 text-slate-400 transition-all">
                    <ArrowLeft class="w-4 h-4" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight uppercase">STOCK OUT</h1>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.2em] mt-1">
                        Reference No: <span class="text-purple-600">#{{ generatedRefNo }}</span>
                    </p>
                </div>
            </div>

            <div v-if="Object.keys(form.errors).length > 0" class="p-4 bg-red-50 border-l-4 border-red-500 flex gap-3 items-center">
                <AlertCircle class="w-5 h-5 text-red-500" />
                <div class="text-xs font-bold text-red-700 uppercase">
                    Error saving transaction. Please check quantities or item selection.
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card class="p-2 border-none ring-1 ring-slate-200 shadow-none overflow-hidden bg-white">
                    <div class="px-3 py-2 border-b border-slate-100 bg-slate-50/50 flex items-center gap-2 mb-4">
                        <Building2 class="w-3.5 h-3.5 text-slate-400" />
                        <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Requisition Details</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 p-3">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Department</label>
                            <select v-model="form.department" class="w-full border-none ring-1 ring-slate-200 rounded-sm text-sm h-10 px-3 bg-white" required :disabled="recentlySubmitted">
                                <option value="" disabled>Select</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.name">{{ dept.name }}</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Released To (Receiver)</label>
                            <input v-model="form.released_to" type="text" placeholder="Full Name" class="w-full border-none ring-1 ring-slate-200 rounded-sm text-sm h-10 px-3 font-semibold" required :disabled="recentlySubmitted" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Release Date</label>
                            <input v-model="form.date_released" type="date" class="w-full border-none ring-1 ring-slate-200 rounded-sm text-sm h-10 px-3 font-semibold" required :disabled="recentlySubmitted" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Purpose / Remarks</label>
                            <input v-model="form.purpose" type="text" placeholder="Optional" class="w-full border-none ring-1 ring-slate-200 rounded-sm text-sm h-10 px-3 transition-all" :disabled="recentlySubmitted" />
                        </div>
                    </div>
                </Card>

                <Card class="p-2 border-none ring-1 ring-slate-200 shadow-none overflow-hidden bg-white">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Property / Item Description</th>
                                <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-500 w-48 border-l border-slate-200 text-center">Qty to Issue</th>
                                <th class="px-4 py-3 w-12 text-center text-slate-300">#</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <tr v-for="(line, index) in form.line_items" :key="index">
                                <td class="px-4 py-3">
                                    <select v-model="line.item_id" class="w-full border-none ring-1 ring-slate-100 rounded-sm text-sm h-9 uppercase" required :disabled="recentlySubmitted">
                                        <option value="" disabled>Select Property...</option>
                                        <option v-for="item in items" :key="item.id" :value="item.id">
                                            {{ item.name }} (Current: {{ item.quantity }})
                                        </option>
                                    </select>
                                    
                                    <div v-if="line.item_id" class="mt-1 ml-1 space-y-1">
                                        <p v-if="props.items.find(i => i.id === line.item_id && line.quantity > i.quantity)" class="text-[9px] text-red-500 font-bold uppercase flex items-center gap-1">
                                            <AlertCircle class="w-3 h-3" /> Exceeds available stock!
                                        </p>
                                        <p v-else-if="props.items.find(i => i.id === line.item_id && (i.quantity - line.quantity) <= i.min_stock)" class="text-[9px] text-amber-500 font-bold uppercase flex items-center gap-1">
                                            <AlertTriangle class="w-3 h-3" /> Will drop below minimum stock
                                        </p>
                                    </div>

                                </td>
                                <td class="px-4 py-3 border-l border-slate-50 text-center">
                                    <input v-model="line.quantity" type="number" step="0.1" min="0.1" class="w-full border-none text-center h-9 ring-1 ring-slate-100 rounded-sm focus:ring-slate-900" required :disabled="recentlySubmitted" />
                                </td>
                                <td class="px-2 py-3 text-center">
                                    <button v-if="!recentlySubmitted" @click="removeItemRow(index)" type="button" class="text-slate-300 hover:text-red-500 transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="!recentlySubmitted" class="p-3 bg-slate-50 border-t border-slate-100 flex justify-center mt-2">
                        <button @click="addItemRow" type="button" class="text-[10px] font-bold text-slate-900 uppercase tracking-[0.2em] flex items-center gap-2 hover:text-purple-900 transition-all">
                            <Plus class="w-3 h-3" /> Add Item Row
                        </button>
                    </div>
                </Card>

                <div class="flex justify-end pt-4">
                    <div v-if="recentlySubmitted" class="flex gap-3">
                        <button @click="triggerExport" type="button" class="inline-flex items-center px-8 py-4 bg-purple-600 hover:bg-purple-700 text-white text-[10px] font-bold rounded-sm shadow-sm transition-all uppercase tracking-[0.2em]">
                            <Download class="w-4 h-4 mr-3" /> Download Issuance PDF
                        </button>
                        <button @click="resetForm" type="button" class="inline-flex items-center px-8 py-4 bg-white ring-1 ring-slate-200 text-slate-600 text-[10px] font-bold rounded-sm hover:bg-slate-50 transition-all uppercase tracking-[0.2em]">
                            <RefreshCw class="w-4 h-4 mr-3" /> New Entry
                        </button>
                    </div>
                    <button v-else type="submit" :disabled="form.processing" class="inline-flex items-center px-10 py-4 bg-slate-900 hover:bg-black text-white text-[10px] font-bold rounded-sm shadow-sm transition-all uppercase tracking-[0.2em] disabled:opacity-50">
                        <Save class="w-4 h-4 mr-3 text-purple-400" />
                        {{ form.processing ? 'Processing...' : 'Save Outbound Entry' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { useForm, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import { 
    ArrowLeft, Save, Loader2, AlertCircle, Plus, Trash2, 
    Hash, Building2, UserCircle, FileText, Calendar 
} from 'lucide-vue-next';

const props = defineProps({ 
    items: Array,
    departments: Array 
});
const page = usePage();

const tempRef = `SO-${Math.floor(1000 + Math.random() * 9000)}`;

const form = useForm({
    released_to: '',
    released_by: page.props.auth.user.name, 
    department: '',
    purpose: '',
    date_released: new Date().toISOString().substr(0, 10),
    line_items: [
        { item_id: '', quantity: 1 }
    ]
});

const addItemRow = () => {
    form.line_items.push({ item_id: '', quantity: 1 });
};

const removeItemRow = (index) => {
    if (form.line_items.length > 1) form.line_items.splice(index, 1);
};

// Logic: Check if any item will fall below the school's minimum stock levels
const hasInsufficientStock = computed(() => {
    return form.line_items.some(line => {
        const item = props.items.find(i => i.id === line.item_id);
        if (!item) return false;
        return (Number(item.quantity) - Number(line.quantity)) < Number(item.min_stock);
    });
});

const submit = () => {
    form.post(route('transactions.store_bulk_out'), {
        onFinish: () => {
            if (!form.hasErrors) form.reset();
        }
    });
};
</script>

<template>
    <Head title="Bulk Issuance" />
    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto pb-10">
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-200">
                <div class="flex items-center gap-4">
                    <Link :href="route('transactions.index')" class="p-2 hover:bg-slate-100 rounded-full transition-all">
                        <ArrowLeft class="w-5 h-5 text-slate-500" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Bulk Stock Out</h1>
                        <p class="text-[10px] text-purple-600 font-black uppercase tracking-widest">Inventory Control Unit</p>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-2 rounded-xl border border-slate-200 text-right">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter leading-none mb-1">Session ID</p>
                    <p class="text-sm font-mono font-bold text-slate-700 tracking-tight">{{ tempRef }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card class="md:col-span-2 p-6 border-none ring-1 ring-slate-200 shadow-sm bg-white rounded-2xl">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="col-span-2 md:col-span-1 space-y-1">
                                <label class="flex items-center gap-2 text-[10px] font-black text-slate-500 uppercase mb-1 ml-1">
                                    <UserCircle class="w-3 h-3" /> Requester / Employee
                                </label>
                                <input v-model="form.released_to" type="text" placeholder="Full Name" class="w-full border-slate-200 rounded-xl text-sm h-11 focus:ring-purple-600 p-2" required />
                            </div>
                            <div class="col-span-2 md:col-span-1 space-y-1">
                                <label class="flex items-center gap-2 text-[10px] font-black text-slate-500 uppercase mb-1 ml-1">
                                    <Building2 class="w-3 h-3" /> Assign to Office
                                </label>
                                <select v-model="form.department" class="w-full border-slate-200 rounded-xl text-sm h-11 focus:ring-purple-600" required>
                                    <option value="" disabled>Select Office...</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.name">{{ dept.name }}</option>
                                </select>
                            </div>
                            <div class="col-span-2 space-y-1">
                                <label class="flex items-center gap-2 text-[10px] font-black text-slate-500 uppercase mb-1 ml-1">
                                    <FileText class="w-3 h-3" /> Purpose / Remarks
                                </label>
                                <input v-model="form.purpose" type="text" placeholder="e.g., Office Supplies for Registrar" class="w-full border-slate-200 rounded-xl text-sm h-11 focus:ring-purple-600 p-2" />
                            </div>
                        </div>
                    </Card>

                    <Card class="p-6 border-none ring-1 ring-slate-200 shadow-sm bg-slate-50/50 rounded-2xl">
                        <div class="space-y-4">
                            <div class="space-y-1">
                                <label class="flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase mb-1 ml-1">
                                    <Calendar class="w-3 h-3" /> Release Date
                                </label>
                                <input v-model="form.date_released" type="date" class="w-full border-slate-200 rounded-xl text-sm bg-white h-11 p-2" required />
                            </div>
                            <div class="p-4 bg-white rounded-xl border border-slate-200">
                                <p class="text-[9px] font-black text-slate-400 uppercase mb-1">Authorized Official</p>
                                <p class="text-xs font-bold text-slate-700 uppercase tracking-tight">{{ form.released_by }}</p>
                            </div>
                        </div>
                    </Card>
                </div>

                <Card class="border-none ring-1 ring-slate-200 shadow-xl bg-white overflow-hidden rounded-2xl">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/80">
                            <tr class="text-[9px] font-black uppercase text-slate-500 tracking-widest border-b border-slate-100">
                                <th class="py-4 px-6">Item Selection</th>
                                <th class="py-4 px-6 w-48">Quantity</th>
                                <th class="py-4 px-6 w-16 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="(line, index) in form.line_items" :key="index" class="group hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 px-6">
                                    <select v-model="line.item_id" class="w-full border-slate-200 rounded-xl text-sm focus:ring-purple-600 h-10" required>
                                        <option value="" disabled>Search item...</option>
                                        <option v-for="item in items" :key="item.id" :value="item.id">
                                            {{ item.name }} (Available: {{ item.quantity }})
                                        </option>
                                    </select>
                                    <div v-if="line.item_id" class="mt-1 ml-1">
                                        <p v-if="props.items.find(i => i.id === line.item_id && (i.quantity - line.quantity) < i.min_stock)" class="text-[9px] text-red-500 font-bold uppercase flex items-center gap-1">
                                            <AlertCircle class="w-3 h-3" /> Critical Stock Level Risk
                                        </p>
                                    </div>
                                </td>
                                <td class="p-4 px-6">
                                    <div class="relative">
                                        <input v-model="line.quantity" type="number" step="0.1" min="0.1" class="w-full border-slate-200 rounded-xl text-sm h-10 pr-12 focus:ring-purple-600 p-2" required />
                                        <span class="absolute right-3 top-2.5 text-[9px] font-black text-slate-300 uppercase">Qty</span>
                                    </div>
                                </td>
                                <td class="p-4 px-6 text-center">
                                    <button @click="removeItemRow(index)" type="button" class="text-slate-300 hover:text-red-500 transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="p-4 bg-slate-50/30 border-t border-slate-100 flex justify-center">
                        <button @click="addItemRow" type="button" class="flex items-center gap-2 text-[10px] font-black text-purple-600 uppercase tracking-widest hover:text-purple-800 transition-all">
                            <Plus class="w-4 h-4" /> Add Item Line
                        </button>
                    </div>
                </Card>

                <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-6 rounded-2xl ring-1 ring-slate-200 shadow-sm">
                    <div class="flex items-center gap-3 text-slate-400">
                        <Hash class="w-4 h-4" />
                        <span class="text-[10px] font-black uppercase tracking-widest">Manifest Count: {{ form.line_items.length }} Items</span>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <p v-if="hasInsufficientStock" class="text-[10px] text-red-500 font-black uppercase">Stock levels too low to proceed</p>
                        <button type="submit" :disabled="form.processing || hasInsufficientStock" 
                            class="bg-slate-900 hover:bg-purple-900 text-white px-12 py-3.5 text-[10px] font-black rounded-xl flex items-center gap-2 uppercase tracking-[0.1em] transition-all shadow-xl shadow-slate-200 disabled:opacity-50">
                            <component :is="form.processing ? Loader2 : Save" class="w-4 h-4" :class="{'animate-spin': form.processing}" />
                            {{ form.processing ? 'Verifying...' : 'Complete Issuance' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
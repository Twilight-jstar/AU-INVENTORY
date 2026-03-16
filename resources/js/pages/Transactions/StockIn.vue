<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import { ArrowLeft, Plus, Trash2, Truck, Save, RefreshCw } from 'lucide-vue-next';

const props = defineProps({ items: Array });

const form = useForm({
    supplier_name: '',
    date_received: new Date().toISOString().substr(0, 10),
    line_items: [{ item_id: '', quantity: 1 }]
});

const addItemRow = () => form.line_items.push({ item_id: '', quantity: 1 });
const removeItemRow = (index) => form.line_items.length > 1 && form.line_items.splice(index, 1);

const submit = () => {
    form.post(route('web.transactions.store_bulk_in'), {
        onSuccess: () => form.reset()
    });
};
</script>

<template>
    <Head title="STOCK IN" />
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('web.transactions.index')" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50"><ArrowLeft class="w-4 h-4" /></Link>
                    <h1 class="text-xl font-black text-slate-900 uppercase">Stock In Entry</h1>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <Card class="p-5 grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase">Supplier Name</label>
                        <input v-model="form.supplier_name" type="text" class="w-full border-slate-200 rounded-lg text-sm" required />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase">Date Received</label>
                        <input v-model="form.date_received" type="date" class="w-full border-slate-200 rounded-lg text-sm" required />
                    </div>
                </Card>

                <Card class="overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr class="text-[10px] font-black uppercase text-slate-500">
                                <th class="p-4">Item Name</th>
                                <th class="p-4 w-32 text-center">Quantity</th>
                                <th class="p-4 w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(line, index) in form.line_items" :key="index">
                                <td class="p-3">
                                    <select v-model="line.item_id" class="w-full border-slate-200 rounded-lg text-sm uppercase" required>
                                        <option value="">Select Item</option>
                                        <option v-for="item in items" :key="item.id" :value="item.id">{{ item.name }} ({{ item.product_code }})</option>
                                    </select>
                                </td>
                                <td class="p-3"><input v-model="line.quantity" type="number" step="0.1" class="w-full border-slate-200 rounded-lg text-sm text-center" required /></td>
                                <td class="p-3"><button type="button" @click="removeItemRow(index)" class="text-slate-300 hover:text-red-500"><Trash2 class="w-4 h-4" /></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="p-3 bg-slate-50 border-t border-slate-100 flex justify-center">
                        <button type="button" @click="addItemRow" class="text-[10px] font-black text-slate-600 uppercase flex items-center gap-2 hover:text-slate-900"><Plus class="w-3 h-3" /> Add Item Row</button>
                    </div>
                </Card>

                <div class="flex justify-end">
                    <button type="submit" :disabled="form.processing" class="bg-slate-900 text-white px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-slate-800 transition-all shadow-lg">
                        <Save class="w-4 h-4" /> {{ form.processing ? 'Saving...' : 'Post Stock In' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
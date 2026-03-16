<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import { ArrowLeft, Save, Trash2, Plus } from 'lucide-vue-next';

const props = defineProps({ items: Array, departments: Array });

const form = useForm({
    department: '',
    released_to: '',
    date_released: new Date().toISOString().substr(0, 10),
    purpose: 'Standard Issuance',
    line_items: [{ item_id: '', quantity: 1 }]
});

const addItemRow = () => form.line_items.push({ item_id: '', quantity: 1 });
const removeItemRow = (index) => form.line_items.length > 1 && form.line_items.splice(index, 1);

const submit = () => {
    form.post(route('web.transactions.store_bulk_out'), {
        onSuccess: () => form.reset()
    });
};
</script>

<template>
    <Head title="STOCK OUT" />
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto p-6 space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('web.transactions.index')" class="p-2 bg-white border border-slate-200 rounded-lg"><ArrowLeft class="w-4 h-4" /></Link>
                <h1 class="text-xl font-black text-slate-900 uppercase">Stock Release Entry</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <Card class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase">Department</label>
                        <select v-model="form.department" class="w-full border-slate-200 rounded-lg text-sm font-bold uppercase" required>
                            <option value="">Select Department</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.name">{{ dept.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase">Released To</label>
                        <input v-model="form.released_to" type="text" class="w-full border-slate-200 rounded-lg text-sm uppercase" placeholder="Name of Recipient" required />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase">Date Released</label>
                        <input v-model="form.date_released" type="date" class="w-full border-slate-200 rounded-lg text-sm" required />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase">Purpose</label>
                        <input v-model="form.purpose" type="text" class="w-full border-slate-200 rounded-lg text-sm" />
                    </div>
                </Card>

                <Card class="overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-100 text-[10px] font-black uppercase text-slate-500">
                            <tr>
                                <th class="p-4">Item (Current Stock)</th>
                                <th class="p-4 w-32 text-center">Qty to Release</th>
                                <th class="p-4 w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(line, index) in form.line_items" :key="index">
                                <td class="p-3">
                                    <select v-model="line.item_id" class="w-full border-slate-200 rounded-lg text-sm uppercase" required>
                                        <option value="">Select Item</option>
                                        <option v-for="item in items" :key="item.id" :value="item.id">{{ item.name }} (Available: {{ item.quantity }})</option>
                                    </select>
                                </td>
                                <td class="p-3"><input v-model="line.quantity" type="number" step="0.1" class="w-full border-slate-200 rounded-lg text-sm text-center" required /></td>
                                <td class="p-3"><button type="button" @click="removeItemRow(index)" class="text-slate-300 hover:text-red-500"><Trash2 class="w-4 h-4" /></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="p-3 bg-slate-50 flex justify-center mt-2 border-t">
                        <button type="button" @click="addItemRow" class="text-[10px] font-black uppercase flex items-center gap-2"><Plus class="w-3 h-3" /> Add Row</button>
                    </div>
                </Card>

                <div class="flex justify-end">
                    <button type="submit" class="bg-slate-900 text-white px-10 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-black shadow-lg">Post Release</button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
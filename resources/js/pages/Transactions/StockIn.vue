<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import { ArrowLeft, Save, Loader2, PackagePlus, Truck, User } from 'lucide-vue-next';

const props = defineProps({ items: Array });

const form = useForm({
    item_id: '',
    quantity: 1,
    received_by: '', 
    date_received: new Date().toISOString().substr(0, 10),
    unit_cost: 0,
    supplier_id: '',
});

const submit = () => {
    form.post(route('transactions.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Stock In" />
    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto pb-10">
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-200">
                <Link :href="route('transactions.index')" class="flex items-center text-slate-500 hover:text-emerald-700 transition-colors text-[10px] font-black uppercase tracking-widest">
                    <ArrowLeft class="w-4 h-4 mr-2" /> Back to Registry
                </Link>
                <div class="text-right">
                    <h1 class="text-xl font-black text-slate-900 uppercase tracking-tight">Inventory Receiving</h1>
                    <p class="text-[9px] text-emerald-600 font-black uppercase tracking-widest">Inbound Movement</p>
                </div>
            </div>

            <Card class="p-8 border-none ring-1 ring-slate-200 shadow-xl bg-white rounded-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-2 ml-1">Item Description</label>
                        <select v-model="form.item_id" class="w-full border-slate-200 rounded-xl text-sm focus:ring-emerald-600 h-11" :class="{'border-red-500': form.errors.item_id}" required>
                            <option value="" disabled>Select Item from Catalog...</option>
                            <option v-for="item in items" :key="item.id" :value="item.id">
                                {{ item.name }} — [Current Stock: {{ item.quantity }}]
                            </option>
                        </select>
                        <div v-if="form.errors.item_id" class="text-red-500 text-[10px] font-bold mt-1 uppercase">{{ form.errors.item_id }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-2 ml-1">Quantity to Add</label>
                            <div class="relative">
                                <input v-model="form.quantity" type="number" step="0.1" min="0.1" class="w-full border-slate-200 rounded-xl text-sm h-11 focus:ring-emerald-600 p-2" required />
                                <span class="absolute right-7 top-4 text-[10px] font-black text-slate-300 uppercase">Units</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-2 ml-1">Unit Cost (₱)</label>
                            <input v-model="form.unit_cost" type="number" step="0.01" class="w-full border-slate-200 rounded-xl text-sm h-11 focus:ring-emerald-600 p-2" placeholder="0.00" />
                        </div>
                    </div>

                    <div class="p-5 bg-emerald-50/50 rounded-2xl border border-emerald-100 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="flex items-center gap-2 text-[10px] font-black text-emerald-700 uppercase mb-2"><Truck class="w-3 h-3"/> Supplier / Source</label>
                            <input v-model="form.supplier_id" type="text" class="w-full border-emerald-200 rounded-xl text-sm bg-white h-10 p-2" placeholder="e.g. National Bookstore" />
                        </div>
                        <div>
                            <label class="flex items-center gap-2 text-[10px] font-black text-emerald-700 uppercase mb-2"><User class="w-3 h-3"/> Received By</label>
                            <input v-model="form.received_by" type="text" class="w-full border-emerald-200 rounded-xl text-sm bg-white h-10 p-2" placeholder="Staff Name" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-2 ml-1">Date of Receipt</label>
                            <input v-model="form.date_received" type="date" class="w-full border-slate-200 rounded-xl text-sm h-11 focus:ring-emerald-600 p-2" required />
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex justify-end">
                        <button type="submit" :disabled="form.processing" 
                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-10 py-3.5 text-[10px] font-black rounded-xl flex items-center gap-2 uppercase tracking-[0.1em] shadow-lg shadow-emerald-100 transition-all disabled:opacity-50">
                            <component :is="form.processing ? Loader2 : PackagePlus" class="w-4 h-4" :class="{'animate-spin': form.processing}" />
                            {{ form.processing ? 'Updating Ledger...' : 'Commit Stock In' }}
                        </button>
                    </div>
                </form>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
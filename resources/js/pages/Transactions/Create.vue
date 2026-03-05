<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import { ArrowLeft, ArrowUpRight, ArrowDownLeft, Save, Loader2 } from 'lucide-vue-next';

defineProps({ items: Array });

const form = useForm({
    items_id: '',
    type: 'In',
    quantity: 1,
    note: ''
});

const submit = () => form.post(route('transactions.store'));
</script>

<template>
    <Head title="Inventory Update" />

    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-200">
                <div class="flex items-center gap-4">
                    <Link 
                        :href="route('transactions.index')" 
                        class="group flex items-center text-slate-500 hover:text-purple-700 transition-colors text-sm font-medium"
                    >
                        <ArrowLeft class="w-4 h-4 mr-1 group-hover:-translate-x-1 transition-transform" />
                        Back to Activity
                    </Link>
                </div>
                <div class="text-right">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Update Stock Levels</h1>
                    <p class="text-xs text-slate-500 font-medium italic">Record stock coming in or going out.</p>
                </div>
            </div>

            <Card class="p-0 border-none ring-1 ring-slate-200 shadow-none overflow-hidden bg-white">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Adjustment Details</span>
                    <span 
                        :class="form.type === 'In' ? 'text-emerald-700 bg-emerald-50 border-emerald-100' : 'text-slate-600 bg-slate-100 border-slate-200'"
                        class="text-[10px] font-bold uppercase px-2 py-0.5 border rounded-sm"
                    >
                        {{ form.type === 'In' ? 'Receiving Items' : 'Releasing Items' }}
                    </span>
                </div>

                <form @submit.prevent="submit" class="p-8 space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Select Item</label>
                        <select 
                            v-model="form.items_id" 
                            class="w-full border-slate-300 rounded-sm px-3 py-2.5 text-sm focus:ring-1 focus:ring-purple-600 focus:border-purple-600 outline-none transition-all bg-white" 
                            required
                        >
                            <option value="" disabled>Choose an item from the list...</option>
                            <option v-for="item in items" :key="item.id" :value="item.id">
                                {{ item.name }} (Current Stock: {{ item.quantity }})
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Update Type</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button 
                                    type="button"
                                    @click="form.type = 'In'"
                                    :class="form.type === 'In' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-500 border-slate-200 hover:border-slate-300'"
                                    class="flex items-center justify-center gap-2 px-3 py-2 text-xs font-bold border rounded-sm transition-all"
                                >
                                    <ArrowUpRight class="w-3.5 h-3.5" />
                                    Stock In
                                </button>
                                <button 
                                    type="button"
                                    @click="form.type = 'Out'"
                                    :class="form.type === 'Out' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-500 border-slate-200 hover:border-slate-300'"
                                    class="flex items-center justify-center gap-2 px-3 py-2 text-xs font-bold border rounded-sm transition-all"
                                >
                                    <ArrowDownLeft class="w-3.5 h-3.5" />
                                    Stock Out
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Quantity</label>
                            <input 
                                v-model="form.quantity" 
                                type="number" 
                                step="0.01" 
                                min="0.1"
                                class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm focus:ring-1 focus:ring-purple-600 outline-none" 
                                required 
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Notes</label>
                        <textarea 
                            v-model="form.note" 
                            rows="3" 
                            placeholder="Add a reason for this update (optional)..."
                            class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm focus:ring-1 focus:ring-purple-600 outline-none transition-all min-h-[100px]"
                        ></textarea>
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                        <Link 
                            :href="route('transactions.index')" 
                            class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest px-4"
                        >
                            Cancel
                        </Link>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="bg-purple-900 hover:bg-slate-900 text-white px-8 py-2.5 text-xs font-bold rounded-sm shadow-sm transition-all uppercase tracking-widest flex items-center gap-2 disabled:opacity-50"
                        >
                            <component :is="form.processing ? Loader2 : Save" class="w-4 h-4" :class="{'animate-spin': form.processing}" />
                            {{ form.processing ? 'Saving...' : 'Save Update' }}
                        </button>
                    </div>
                </form>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
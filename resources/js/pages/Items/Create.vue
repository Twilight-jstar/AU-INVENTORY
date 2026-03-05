<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import { ArrowLeft, Save, Loader2, Info } from 'lucide-vue-next';

defineProps({
    categories: Array,
    units: Array
});

const form = useForm({
    product_code: '',
    name: '',
    quantity: 0,
    category_id: '',
    unit_id: '',
    description: ''
});

const submit = () => {
    form.post(route('items.store'));
};
</script>

<template>
    <Head title="Inventory Enrollment" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-200">
                <div class="flex items-center gap-4">
                    <Link 
                        :href="route('items.index')" 
                        class="group flex items-center text-slate-500 hover:text-purple-700 transition-colors"
                    >
                        <ArrowLeft class="w-4 h-4 mr-1 group-hover:-translate-x-1 transition-transform" />
                        <span class="text-sm font-medium">Back to Items</span>
                    </Link>
                </div>
                <div class="text-right">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Add New Item</h1>
                    <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold">Asset Entry Form</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1">
                    <div class="space-y-6">
                        <Card class="p-6 border-slate-200 shadow-none ring-1 ring-slate-200 bg-white">
                            <h3 class="text-sm font-bold text-slate-800 mb-2">Instructions</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Please ensure the <span class="text-purple-700 font-semibold">Product Code</span> matches the physical tag on the asset for audit consistency.
                            </p>
                        </Card>
                        <Card class="p-4 bg-slate-100 rounded border border-slate-200">
                            <div class="flex gap-3">
                                <Info class="w-5 h-5 text-slate-400 shrink-0" />
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Fields marked with an asterisk are required for the official registry.
                                </p>
                            </div>
                        </Card>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <Card class="p-0 border-slate-200 shadow-none ring-1 ring-slate-200 overflow-hidden bg-white">
                        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                            <span class="text-xs font-bold text-slate-500 uppercase">Item Specifications</span>
                        </div>
                        
                        <form @submit.prevent="submit" class="p-6 space-y-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Product Code *</label>
                                <input 
                                    v-model="form.product_code" 
                                    type="text" 
                                    placeholder="e.g. LAW-LIB-2024-01"
                                    class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm focus:ring-1 focus:ring-purple-600 focus:border-purple-600 outline-none transition-all placeholder:text-slate-300" 
                                    required 
                                />
                                <div v-if="form.errors.product_code" class="text-red-600 text-[11px] mt-1 font-semibold">{{ form.errors.product_code }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Item Name *</label>
                                <input 
                                    v-model="form.name" 
                                    type="text" 
                                    class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm focus:ring-1 focus:ring-purple-600 focus:border-purple-600 outline-none" 
                                    required 
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Category</label>
                                    <select v-model="form.category_id" class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm focus:ring-1 focus:ring-purple-600 outline-none bg-white">
                                        <option value="">Select Classification</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Unit of Measure</label>
                                    <select v-model="form.unit_id" class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm focus:ring-1 focus:ring-purple-600 outline-none bg-white">
                                        <option value="">Select Unit</option>
                                        <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="w-1/2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Initial Quantity</label>
                                <input 
                                    v-model="form.quantity" 
                                    type="number" 
                                    class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm focus:ring-1 focus:ring-purple-600 outline-none" 
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Additional Description</label>
                                <textarea 
                                    v-model="form.description" 
                                    class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm focus:ring-1 focus:ring-purple-600 outline-none min-h-[100px]" 
                                    placeholder="Enter asset details or serial numbers..."
                                ></textarea>
                            </div>

                            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                                <Link :href="route('items.index')" class="text-sm font-semibold text-slate-500 hover:text-slate-700 px-4">
                                    Cancel
                                </Link>
                                <button 
                                    type="submit" 
                                    :disabled="form.processing" 
                                    class="bg-purple-900 hover:bg-slate-900 text-white px-6 py-2 text-sm font-bold rounded-sm shadow-sm transition-all flex items-center gap-2 disabled:opacity-50"
                                >
                                    <component :is="form.processing ? Loader2 : Save" class="w-4 h-4" :class="{'animate-spin': form.processing}" />
                                    {{ form.processing ? 'Processing...' : 'Register Item' }}
                                </button>
                            </div>
                        </form>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
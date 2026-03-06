<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import { Ruler, Trash2, Plus, Loader2, Scale } from 'lucide-vue-next';

defineProps({ units: Array });

const form = useForm({ name: '' });

const submit = () => {
    form.post(route('units.store'), { 
        onSuccess: () => form.reset() 
    });
};

const deleteUnit = (id) => {
    if (confirm('Deleting this unit might affect items using it. Continue with removal?')) {
        form.delete(route('units.destroy', id));
    }
};
</script>

<template>
    <Head title="Units of Measurement" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="flex items-center justify-between border-b border-slate-200 pb-5">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Measurement Standards</h1>
                    <p class="text-sm text-slate-500 italic">Manage metrics used for asset quantification and stock tracking.</p>
                </div>
                <Scale class="w-8 h-8 text-slate-300" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <div class="md:col-span-4">
                    <Card class="p-6 border-slate-200 shadow-none ring-1 ring-slate-200 bg-white sticky top-6">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <Plus class="w-3 h-3" />
                            Define New Unit
                        </h3>
                        
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Unit Name</label>
                                <input 
                                    v-model="form.name" 
                                    type="text" 
                                    placeholder="e.g., Kilograms, Pieces" 
                                    class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm focus:ring-1 focus:ring-purple-600 focus:border-purple-600 outline-none transition-all placeholder:text-slate-300" 
                                    required 
                                />
                            </div>
                            
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="w-full bg-slate-900 hover:bg-purple-900 text-white px-4 py-2.5 text-xs font-bold rounded-sm shadow-sm transition-all uppercase tracking-widest flex items-center justify-center gap-2"
                            >
                                <Loader2 v-if="form.processing" class="w-3.5 h-3.5 animate-spin" />
                                {{ form.processing ? 'Processing' : 'Register Unit' }}
                            </button>
                        </form>
                    </Card>
                </div>

                <div class="md:col-span-8">
                    <Card class="p-0 border-none ring-1 ring-slate-200 shadow-none overflow-hidden">
                        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Authorized Metrics</span>
                            <span class="text-[10px] bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full font-mono">{{ units.length }} Registered</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="text-slate-600 text-[11px] font-bold uppercase tracking-wider border-b border-slate-100">
                                        <th class="py-3 px-6">Metric Nomenclature</th>
                                        <th class="py-3 px-6 text-right w-32">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="text-slate-700 text-sm divide-y divide-slate-50">
                                    <tr v-for="unit in units" :key="unit.id" class="hover:bg-slate-50/50 transition-colors group">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-1.5 h-1.5 rounded-none rotate-45 border border-purple-400 group-hover:bg-purple-600 transition-colors"></div>
                                                <div>
                                                    <div class="font-medium text-slate-900">{{ unit.name }}</div>
                                                    <div class="text-[10px] text-slate-400 uppercase tracking-tighter">Inventory Standard</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <button 
                                                @click="deleteUnit(unit.id)" 
                                                class="text-slate-300 hover:text-red-700 transition-colors p-1"
                                                title="Remove Unit"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <tr v-if="units.length === 0">
                                        <td colspan="2" class="py-16 text-center">
                                            <Ruler class="w-8 h-8 text-slate-200 mx-auto mb-2" />
                                            <p class="text-xs text-slate-400 italic font-medium">No measurement units defined in the registry.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
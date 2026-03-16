<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import { Ruler, Trash2, Plus, Loader2, Scale, Pencil, X, Check } from 'lucide-vue-next';

const props = defineProps({ units: Array });

const form = useForm({ name: '' });
const editingId = ref(null);
const editForm = useForm({ name: '' });

const submit = () => {
    form.post(route('units.store'), { 
        onSuccess: () => form.reset() 
    });
};

const startEdit = (unit) => {
    editingId.value = unit.id;
    editForm.name = unit.name;
};

const cancelEdit = () => {
    editingId.value = null;
    editForm.reset();
};

const updateUnit = (id) => {
    editForm.put(route('units.update', id), {
        onSuccess: () => editingId.value = null,
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
                    <p class="text-sm text-slate-500 italic">Manage metrics used for asset quantification.</p>
                </div>
                <Scale class="w-8 h-8 text-slate-300" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <div class="md:col-span-4">
                    <Card class="p-6 border-slate-200 shadow-none ring-1 ring-slate-200 bg-white sticky top-6">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <Plus class="w-3 h-3" /> Define New Unit
                        </h3>
                        
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Unit Name</label>
                                <input v-model="form.name" type="text" placeholder="e.g., Kilograms" class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm focus:ring-purple-600 transition-all outline-none" required />
                                <div v-if="form.errors.name" class="text-red-500 text-[10px] mt-1">{{ form.errors.name }}</div>
                            </div>
                            
                            <button type="submit" :disabled="form.processing" class="w-full bg-slate-900 hover:bg-purple-900 text-white px-4 py-2.5 text-xs font-bold rounded-sm transition-all uppercase tracking-widest flex items-center justify-center gap-2">
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
                                        <th class="py-3 px-6 text-right w-40">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="text-slate-700 text-sm divide-y divide-slate-50">
                                    <tr v-for="unit in units" :key="unit.id" class="hover:bg-slate-50/50 transition-colors group">
                                        <td class="py-4 px-6">
                                            <div v-if="editingId === unit.id">
                                                <input v-model="editForm.name" type="text" class="text-sm border-slate-300 rounded px-2 py-1 focus:ring-purple-600 w-full" @keyup.enter="updateUnit(unit.id)" />
                                                <div v-if="editForm.errors.name" class="text-red-500 text-[10px] mt-1">{{ editForm.errors.name }}</div>
                                            </div>
                                            <div v-else class="flex items-center gap-3">
                                                <div class="w-1.5 h-1.5 rounded-none rotate-45 border border-purple-400 group-hover:bg-purple-600 transition-colors"></div>
                                                <span class="font-medium text-slate-900">{{ unit.name }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <div v-if="editingId === unit.id" class="flex justify-end gap-2">
                                                <button @click="updateUnit(unit.id)" class="text-green-600 hover:text-green-800 p-1" :disabled="editForm.processing"><Check class="w-4 h-4" /></button>
                                                <button @click="cancelEdit" class="text-slate-400 hover:text-slate-600 p-1"><X class="w-4 h-4" /></button>
                                            </div>
                                            <div v-else class="flex justify-end gap-2">
                                                <button @click="startEdit(unit)" class="text-slate-300 hover:text-purple-700 p-1"><Pencil class="w-4 h-4" /></button>
                                                <button @click="deleteUnit(unit.id)" class="text-slate-300 hover:text-red-700 p-1"><Trash2 class="w-4 h-4" /></button>
                                            </div>
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
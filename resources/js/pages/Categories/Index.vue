<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Card from '@/components/ui/card/Card.vue';
import { Tag, Trash2, Plus, Loader2, FolderTree, Pencil, X, Check } from 'lucide-vue-next';

const props = defineProps({ categories: Array });

const form = useForm({ name: '' });
const editingId = ref(null);
const editForm = useForm({ name: '' });

const submit = () => {
    form.post(route('categories.store'), {
        onSuccess: () => form.reset(),
    });
};

const startEdit = (category) => {
    editingId.value = category.id;
    editForm.name = category.name;
};

const cancelEdit = () => {
    editingId.value = null;
    editForm.reset();
};

const updateCategory = (id) => {
    editForm.put(route('categories.update', id), {
        onSuccess: () => editingId.value = null,
    });
};

const deleteCategory = (id) => {
    if (confirm('Are you sure you want to remove this classification?')) {
        form.delete(route('categories.destroy', id));
    }
};
</script>

<template>
    <Head title="Classifications" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="flex items-center justify-between border-b border-slate-200 pb-5">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Asset Classifications</h1>
                    <p class="text-sm text-slate-500 italic">Define organizational groupings for inventory assets.</p>
                </div>
                <FolderTree class="w-8 h-8 text-slate-300" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <div class="md:col-span-4">
                    <Card class="p-6 border-slate-200 shadow-none ring-1 ring-slate-200 bg-white sticky top-6">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <Plus class="w-3 h-3" /> New Classification
                        </h3>
                        
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Name</label>
                                <input v-model="form.name" type="text" placeholder="e.g., Office Supplies" class="w-full border-slate-300 rounded-sm px-3 py-2 text-sm focus:ring-1 focus:ring-purple-600 focus:border-purple-600 outline-none transition-all" required />
                                <div v-if="form.errors.name" class="text-red-500 text-[10px] mt-1">{{ form.errors.name }}</div>
                            </div>
                            
                            <button type="submit" :disabled="form.processing" class="w-full bg-slate-900 hover:bg-purple-900 text-white px-4 py-2.5 text-xs font-bold rounded-sm shadow-sm transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                                <Loader2 v-if="form.processing" class="w-3.5 h-3.5 animate-spin" />
                                {{ form.processing ? 'Adding...' : 'Add to Registry' }}
                            </button>
                        </form>
                    </Card>
                </div>

                <div class="md:col-span-8">
                    <Card class="p-0 border-none ring-1 ring-slate-200 shadow-none overflow-hidden">
                        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Active Classifications</span>
                            <span class="text-[10px] bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full font-mono">{{ categories.length }} Total</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="text-slate-600 text-[11px] font-bold uppercase tracking-wider border-b border-slate-100">
                                        <th class="py-3 px-6">Classification Name</th>
                                        <th class="py-3 px-6 text-right w-40">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="text-slate-700 text-sm divide-y divide-slate-50">
                                    <tr v-for="category in categories" :key="category.id" class="hover:bg-slate-50/50 transition-colors group">
                                        <td class="py-4 px-6">
                                            <div v-if="editingId === category.id" class="flex items-center gap-2">
                                                <input v-model="editForm.name" type="text" class="text-sm border-slate-300 rounded px-2 py-1 focus:ring-purple-600 w-full" @keyup.enter="updateCategory(category.id)" @keyup.esc="cancelEdit" auto-focus />
                                            </div>
                                            <div v-else class="flex items-center gap-3">
                                                <div class="w-1.5 h-1.5 rounded-full bg-purple-200 group-hover:bg-purple-600 transition-colors"></div>
                                                <span class="font-medium text-slate-900">{{ category.name }}</span>
                                            </div>
                                            <div v-if="editingId === category.id && editForm.errors.name" class="text-red-500 text-[10px] mt-1">{{ editForm.errors.name }}</div>
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <div v-if="editingId === category.id" class="flex justify-end gap-2">
                                                <button @click="updateCategory(category.id)" class="text-green-600 hover:text-green-800 p-1" :disabled="editForm.processing">
                                                    <Check class="w-4 h-4" />
                                                </button>
                                                <button @click="cancelEdit" class="text-slate-400 hover:text-slate-600 p-1">
                                                    <X class="w-4 h-4" />
                                                </button>
                                            </div>
                                            <div v-else class="flex justify-end gap-2">
                                                <button @click="startEdit(category)" class="text-slate-400 hover:text-purple-700 p-1" title="Edit">
                                                    <Pencil class="w-4 h-4" />
                                                </button>
                                                <button @click="deleteCategory(category.id)" class="text-slate-400 hover:text-red-700 p-1" title="Delete">
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
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
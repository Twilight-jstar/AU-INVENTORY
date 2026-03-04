<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

defineProps({ units: Array });

const form = useForm({ name: '' });

const submit = () => {
    form.post(route('units.store'), { 
        onSuccess: () => form.reset() 
    });
};
</script>

<template>
    <Head title="Units of Measurement" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto py-6">
            <h1 class="text-2xl font-bold mb-6 text-gray-800">Units of Measurement</h1>
            
            <form @submit.prevent="submit" class="mb-8 flex gap-4 bg-white p-4 rounded-lg shadow-sm border items-end">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">New Unit Name</label>
                    <input 
                        v-model="form.name" 
                        type="text" 
                        placeholder="e.g., Kilograms, Pieces, Liters" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none" 
                        required 
                    />
                </div>
                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md font-bold transition-colors h-[42px] disabled:opacity-50"
                >
                    {{ form.processing ? 'Saving...' : 'Add Unit' }}
                </button>
            </form>

            <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="p-4 font-semibold text-gray-700">Unit Name</th>
                            <th class="p-4 text-right font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="unit in units" :key="unit.id" class="border-b last:border-0 hover:bg-gray-50 transition-colors">
                            <td class="p-4">
                                <div class="font-medium text-gray-900">{{ unit.name }}</div>
                                <div class="text-xs text-gray-400">Used for tracking stock levels</div>
                            </td>
                            <td class="p-4 text-right">
                                <button 
                                    @click="$inertia.delete(route('units.destroy', unit.id))" 
                                    class="text-red-500 hover:text-red-700 text-sm font-semibold transition-colors"
                                    onBefore="return confirm('Deleting this unit might affect items using it. Continue?')"
                                >
                                    Remove
                                </button>
                            </td>
                        </tr>
                        <tr v-if="units.length === 0">
                            <td colspan="2" class="p-12 text-center text-gray-500 italic">
                                No units defined. Please add units like "kg" or "pcs" to begin.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
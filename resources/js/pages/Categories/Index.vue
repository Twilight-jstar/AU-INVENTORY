<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'; //

defineProps({ categories: Array });

const form = useForm({ name: '' });

const submit = () => {
    form.post(route('categories.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Categories" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto">
            <h1 class="text-2xl font-bold mb-6">Manage Categories</h1>
            
            <form @submit.prevent="submit" class="mb-8 flex gap-4 bg-white p-4 rounded-lg shadow-sm border">
                <input 
                    v-model="form.name" 
                    type="text" 
                    placeholder="New Category Name" 
                    class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                    required 
                />
                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-bold transition-colors disabled:opacity-50"
                >
                    {{ form.processing ? 'Adding...' : 'Add' }}
                </button>
            </form>

            <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="p-4 font-semibold text-gray-700">Name</th>
                            <th class="p-4 text-right font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="category in categories" :key="category.id" class="border-b last:border-0 hover:bg-gray-50">
                            <td class="p-4 text-gray-800">{{ category.name }}</td>
                            <td class="p-4 text-right">
                                <button 
                                    @click="$inertia.delete(route('categories.destroy', category.id))" 
                                    class="text-red-500 hover:text-red-700 font-medium"
                                    onBefore="return confirm('Are you sure you want to delete this category?')"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="categories.length === 0">
                            <td colspan="2" class="p-8 text-center text-gray-500">
                                No categories found. Add one above to get started.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

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
    // Use the route name defined in web.php
    form.post(route('items.store'));
};
</script>

<template>
    <Head title="Add Item" />

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto py-6">
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('items.index')" class="text-gray-500 hover:text-gray-700">
                    ← Back
                </Link>
                <h1 class="text-2xl font-bold">Add New Item</h1>
            </div>
            
            <form @submit.prevent="submit" class="bg-white p-6 rounded-lg shadow-sm border space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Product Code</label>
                    <input v-model="form.product_code" type="text" class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required />
                    <div v-if="form.errors.product_code" class="text-red-500 text-xs mt-1">{{ form.errors.product_code }}</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Item Name</label>
                        <input v-model="form.name" type="text" class="w-full border rounded-md px-3 py-2" required />
                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Initial Quantity</label>
                        <input v-model="form.quantity" type="number" step="0.01" class="w-full border rounded-md px-3 py-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Category</label>
                        <select v-model="form.category_id" class="w-full border rounded-md px-3 py-2">
                            <option value="">Select Category</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                        <div v-if="form.errors.category_id" class="text-red-500 text-xs mt-1">{{ form.errors.category_id }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Unit</label>
                        <select v-model="form.unit_id" class="w-full border rounded-md px-3 py-2">
                            <option value="">Select Unit</option>
                            <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Description</label>
                    <textarea v-model="form.description" class="w-full border rounded-md px-3 py-2" rows="3"></textarea>
                </div>

                <button 
                    type="submit" 
                    :disabled="form.processing" 
                    class="w-full bg-green-600 text-white py-3 rounded-md font-bold hover:bg-green-700 transition-colors disabled:opacity-50"
                >
                    {{ form.processing ? 'Saving...' : 'Save Item' }}
                </button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
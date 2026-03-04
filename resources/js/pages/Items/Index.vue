<script setup>
import { Link, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

defineProps({
    items: Array
});
</script>

<template>
    <Head title="Inventory Items" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Inventory Items</h1>
                <Link 
                    :href="route('items.create')" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition-colors font-medium flex items-center gap-2"
                >
                    <span class="text-xl">+</span> Add New Item
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold tracking-wider border-b">
                                <th class="py-4 px-6 text-left">Code</th>
                                <th class="py-4 px-6 text-left">Name</th>
                                <th class="py-4 px-6 text-left">Category</th>
                                <th class="py-4 px-6 text-left">Stock</th>
                                <th class="py-4 px-6 text-left">Unit</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            <tr v-for="item in items" :key="item.id" class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-6 font-mono text-xs">{{ item.product_code }}</td>
                                <td class="py-4 px-6 font-medium text-gray-900">{{ item.name }}</td>
                                <td class="py-4 px-6">
                                    <span v-if="item.category" class="bg-blue-50 text-blue-700 px-2 py-1 rounded text-xs">
                                        {{ item.category.name }}
                                    </span>
                                    <span v-else class="text-gray-400">N/A</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span :class="item.quantity <= 5 ? 'text-red-600 font-bold' : ''">
                                        {{ item.quantity }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-gray-500">{{ item.unit?.name || 'N/A' }}</td>
                            </tr>
                            <tr v-if="items.length === 0">
                                <td colspan="5" class="py-12 text-center text-gray-500">
                                    No items found. Click "+ Add New Item" to start your inventory.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
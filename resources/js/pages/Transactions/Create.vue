<script setup>
import { useForm } from '@inertiajs/vue3';

defineProps({ items: Array });

const form = useForm({
    items_id: '',
    type: 'In',
    quantity: 1
});

const submit = () => form.post(route('transactions.store'));
</script>

<template>
    <div class="max-w-xl mx-auto p-8">
        <h1 class="text-2xl font-bold mb-6">Stock In / Stock Out</h1>
        <form @submit.prevent="submit" class="bg-white p-6 rounded shadow space-y-4">
            <div>
                <label class="block font-bold">Select Item</label>
                <select v-model="form.items_id" class="w-full border p-2 rounded" required>
                    <option v-for="item in items" :key="item.id" :value="item.id">{{ item.name }} (Current: {{ item.quantity }})</option>
                </select>
            </div>
            <div>
                <label class="block font-bold">Movement Type</label>
                <select v-model="form.type" class="w-full border p-2 rounded">
                    <option value="In">Stock In (+)</option>
                    <option value="Out">Stock Out (-)</option>
                </select>
            </div>
            <div>
                <label class="block font-bold">Quantity</label>
                <input v-model="form.quantity" type="number" step="0.01" class="w-full border p-2 rounded" required />
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded font-bold">Submit Transaction</button>
        </form>
    </div>
</template>

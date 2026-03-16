<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
// Fixed casing for layouts folder to resolve TS error
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/components/ui/select';
import { Save, ArrowLeft } from 'lucide-vue-next';
import { route } from 'ziggy-js';

const props = defineProps<{
    item: {
        id: number;
        product_code: string;
        name: string;
        min_stock: number;
        description: string;
        category_id: number;
        unit_id: number;
    };
    categories: Array<{ id: number; name: string }>;
    units: Array<{ id: number; name: string }>;
}>();

const form = useForm({
    product_code: props.item.product_code,
    name: props.item.name,
    min_stock: props.item.min_stock,
    category_id: props.item.category_id?.toString(),
    unit_id: props.item.unit_id?.toString(),
    description: props.item.description || '',
});

const submit = () => {
    form.put(route('web.items.update', props.item.id));
};
</script>

<template>
    <Head :title="`Edit ${item.name}`" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('web.items.index')" class="p-2 hover:bg-slate-200 rounded-full transition-colors">
                        <ArrowLeft class="w-5 h-5 text-slate-600" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Edit Item</h1>
                        <p class="text-sm text-slate-500">Update details for {{ item.product_code }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <form @submit.prevent="submit" class="p-8 space-y-8">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="grid gap-2">
                            <Label for="product_code">Product Code</Label>
                            <Input 
                                id="product_code" 
                                v-model="form.product_code" 
                                placeholder="e.g. IT-001"
                                :class="{ 'border-red-500': form.errors.product_code }"
                            />
                            <InputError :message="form.errors.product_code" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="name">Item Name</Label>
                            <Input 
                                id="name" 
                                v-model="form.name" 
                                placeholder="e.g. Wireless Mouse"
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Category</Label>
                            <Select v-model="form.category_id">
                                <SelectTrigger :class="{ 'border-red-500': form.errors.category_id }">
                                    <SelectValue placeholder="Select Category" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id.toString()">
                                        {{ cat.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.category_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Unit of Measure</Label>
                            <Select v-model="form.unit_id">
                                <SelectTrigger :class="{ 'border-red-500': form.errors.unit_id }">
                                    <SelectValue placeholder="Select Unit" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="unit in units" :key="unit.id" :value="unit.id.toString()">
                                        {{ unit.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.unit_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="min_stock">Minimum Stock Alert Level</Label>
                            <Input 
                                id="min_stock" 
                                type="number" 
                                v-model="form.min_stock" 
                                :class="{ 'border-red-500': form.errors.min_stock }"
                            />
                            <small class="text-slate-400">System will flag item if quantity falls below this number.</small>
                            <InputError :message="form.errors.min_stock" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description (Optional)</Label>
                        <textarea 
                            id="description" 
                            v-model="form.description" 
                            rows="4"
                            placeholder="Detailed specifications..."
                            class="flex min-h-[80px] w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-purple-600 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        ></textarea>
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                        <Link 
                            :href="route('web.items.index')"
                            class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-slate-200 bg-white hover:bg-slate-100 hover:text-slate-900 h-10 px-4 py-2"
                        >
                            Cancel
                        </Link>
                        <Button 
                            type="submit" 
                            class="bg-purple-600 hover:bg-purple-700 text-white gap-2"
                            :disabled="form.processing"
                        >
                            <Save class="w-4 h-4" />
                            Update Item
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../layouts/AuthenticatedLayout.vue';
import { UserPlus, Pencil, Trash2, Shield, X } from 'lucide-vue-next';

const props = defineProps({
    users: Array,
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    username: '',
    email: '',
    role: 'Viewer',
    password: '', 
});


const openAddModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    isModalOpen.value = true;
};


const openEditModal = (user) => {
    isEditing.value = true;
    editingId.value = user.id;
    
    form.name = user.name;
    form.username = user.username;
    form.email = user.email;
    form.role = user.role;
    form.password = ''; 
    
    isModalOpen.value = true;
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('users.update', editingId.value), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('users.store'), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    }
};

const deleteUser = (id) => {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        useForm({}).delete(route('users.destroy', id));
    }
};
</script>

<template>
    <Head title="Manage Users" />

    <AuthenticatedLayout>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <Shield class="w-5 h-5 text-purple-600" />
                        System Users
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Manage who can access the ALF Inventory system.</p>
                </div>
                
                <button @click="openAddModal" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 transition-colors shadow-sm">
                    <UserPlus class="w-4 h-4" />
                    Add New User
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-bold text-slate-500 tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Username</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ user.name }}</td>
                            <td class="px-6 py-4">{{ user.username }}</td>
                            <td class="px-6 py-4">{{ user.email }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider"
                                    :class="{
                                        'bg-purple-100 text-purple-700': user.role === 'Admin',
                                        'bg-blue-100 text-blue-700': user.role === 'Custodian',
                                        'bg-emerald-100 text-emerald-700': user.role === 'Clerk',
                                        'bg-slate-100 text-slate-700': user.role === 'Viewer'
                                    }">
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 flex items-center justify-end gap-2">
                                <button @click="openEditModal(user)" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                    <Pencil class="w-4 h-4" />
                                </button>
                                
                                <button @click="deleteUser(user.id)" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-purple-50/50">
                    <h3 class="font-bold text-purple-900 flex items-center gap-2">
                        <UserPlus v-if="!isEditing" class="w-5 h-5" />
                        <Pencil v-else class="w-5 h-5" />
                        {{ isEditing ? 'Edit System User' : 'Add New System User' }}
                    </h3>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-500 mb-1 ml-1">Full Name</label>
                        <input v-model="form.name" type="text" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500 outline-none text-sm" placeholder="Juan Dela Cruz" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-500 mb-1 ml-1">Username</label>
                            <input v-model="form.username" type="text" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500 outline-none text-sm" placeholder="juan123" required>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-500 mb-1 ml-1">Role</label>
                            <select v-model="form.role" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500 outline-none text-sm appearance-none">
                                <option value="Admin">Admin</option>
                                <option value="Custodian">Custodian</option>
                                <option value="Clerk">Clerk</option>
                                <option value="Viewer">Viewer</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase text-slate-500 mb-1 ml-1">Email Address</label>
                        <input v-model="form.email" type="email" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500 outline-none text-sm" placeholder="juan@alf.com" required>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase text-slate-500 mb-1 ml-1">
                            {{ isEditing ? 'New Password (Leave blank to keep current)' : 'Initial Password' }}
                        </label>
                        <input v-model="form.password" type="password" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500 outline-none text-sm" placeholder="••••••••" :required="!isEditing">
                    </div>

                    <div class="pt-2 flex gap-3">
                        <button type="button" @click="isModalOpen = false" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                            CANCEL
                        </button>
                        <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2.5 rounded-xl bg-purple-600 text-sm font-bold text-white hover:bg-purple-700 shadow-lg shadow-purple-200 transition-all disabled:opacity-50">
                            {{ form.processing ? 'SAVING...' : (isEditing ? 'UPDATE USER' : 'SAVE USER') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
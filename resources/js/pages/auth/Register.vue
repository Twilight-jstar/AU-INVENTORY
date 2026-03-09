<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { route } from 'ziggy-js';

const form = useForm({
    name: '',
    username: '',
    email: '',
    role: '',
    password: '',
    password_confirmation: '',
});

// Titingnan nito kung na-meet na ng user yung lahat ng rules
const isPasswordValid = computed(() => {
    const p = form.password;
    if (!p) return false;
    
    const hasUpper = /[A-Z]/.test(p);
    const hasLower = /[a-z]/.test(p);
    const hasNumber = /\d/.test(p);
    const hasSymbol = /[\W_]/.test(p); // Che-check kung may special character
    const isLengthValid = p.length >= 8 && p.length <= 32;

    return hasUpper && hasLower && hasNumber && hasSymbol && isLengthValid;
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase
        title="Create an account"
        description="Enter your details below to create your account"
    >
        <Head title="Register" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            autofocus
                            :tabindex="1"
                            placeholder="Full name"
                        />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="username">Username</Label>
                        <Input
                            id="username"
                            v-model="form.username"
                            type="text"
                            required
                            :tabindex="2"
                            placeholder="Username"
                        />
                        <InputError :message="form.errors.username" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            :tabindex="3"
                            placeholder="email@example.com"
                        />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="role">User Role</Label>
                        <select 
                            id="role" 
                            v-model="form.role"
                            required 
                            :tabindex="4"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring"
                        >
                            <option value="" disabled selected>Select role</option>
                            <option value="Admin">Admin</option>
                            <option value="Clerk">Clerk</option>
                            <option value="Custodian">Custodian</option>
                        </select>
                        <InputError :message="form.errors.role" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            :tabindex="5"
                            placeholder="••••••••"
                        />
                        <p v-if="!isPasswordValid" class="text-[0.8rem] text-muted-foreground">
                            Must be 8-32 characters, with an uppercase, lowercase, number, and symbol.
                        </p>
                        <InputError :message="form.errors.password" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirm</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            :tabindex="6"
                            placeholder="••••••••"
                        />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full"
                    tabindex="7"
                    :disabled="form.processing"
                >
                    <Spinner v-if="form.processing" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="8">
                    Log in
                </TextLink>
            </div>
        </form>
    </AuthBase>
</template>
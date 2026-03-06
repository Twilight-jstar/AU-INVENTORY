<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'; // Import useForm
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { route } from 'ziggy-js';
import register from '@/routes/register';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

// Initialize the form data properly
const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase title="Log in" description="Enter your credentials">
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="username">Username</Label>
                    <Input
                        id="username"
                        v-model="form.username" 
                        type="text"
                        required
                        autofocus
                        placeholder="Your username"
                    />
                    <InputError :message="form.errors.username" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm">
                            Forgot password?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center space-x-3">
                    <Checkbox 
                        id="remember" 
                        :checked="form.remember" 
                        @update:checked="(val: boolean) => form.remember = val" 
                    />
                    <Label for="remember">Remember me</Label>
                </div>

                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    Log in
                </Button>
            </div>

            <div
                class="text-center text-sm text-muted-foreground"
                v-if="canRegister"
            >
                Don't have an account?
                <TextLink :href="route('register')" :tabindex="5">Sign up</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
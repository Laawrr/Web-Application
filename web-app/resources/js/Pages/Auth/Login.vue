<template>
    <div class="w-full sm:max-w-lg">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Welcome To Lost Finder</h2>
            <p class="mt-2 text-sm text-gray-600">Please sign in to your account</p>
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>
        
        <InputError class="mt-2" :message="form.errors.email" />

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />
            </div>

            <div>
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600">Remember me</span>
                </div>
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-teal-500 hover:text-teal-600"
                >
                    Forgot password?
                </Link>
            </div>

            <div>
                <PrimaryButton
                    class="w-full flex justify-center items-center space-x-2 bg-teal-500 text-white px-4 py-2 rounded-lg hover:bg-teal-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <LoadingSpinner v-if="form.processing" />
                    <span>Sign in</span>
                </PrimaryButton>
            </div>

            <div class="text-center mt-4">
                <span class="text-sm text-gray-600">Don't have an account?</span>
                <a :href="route('register')" class="ml-1 text-sm text-teal-500 hover:text-teal-600 cursor-pointer">Sign up</a>
            </div>
        </form>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

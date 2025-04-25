<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({});
</script>

<template>
    <GuestLayout>
        <Head title="Pending Approval" />

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Your account is currently pending approval by an admin. You will receive an email once your account is approved.
        </div>

        <form @submit.prevent="form.post(route('logout'))">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log Out
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

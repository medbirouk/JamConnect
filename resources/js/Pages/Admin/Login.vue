<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue';

const form = useForm({
    email: '',
    password: '',
})

const submit = () => {
    form.post(route('admin.login'))
}
</script>



<template>
    
        <div class="min-h-screen flex items-center justify-center bg-gray-100">
            <form @submit.prevent="submit" class="bg-white p-6 rounded shadow-md w-full max-w-md">
                <h1 class="text-2xl font-bold mb-4">Admin Login</h1>

                <div class="mb-4">
                    <label class="block text-gray-700">Email</label>
                    <input v-model="form.email" type="email" class="mt-1 w-full border rounded px-3 py-2" required />
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700">Password</label>
                    <input v-model="form.password" type="password" class="mt-1 w-full border rounded px-3 py-2"
                        required />
                </div>

                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Login
                </button>
            </form>
        </div>
    
</template>

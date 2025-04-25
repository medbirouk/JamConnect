<script setup>
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3';
defineProps({
    pendingUsers: Array,
})
function approveUser(id) {
    router.post(route('admin.users.approve', id), {}, {
        preserveScroll: true,
    })
}
function rejectUser(id) {
    router.post(route('admin.users.reject', id), {}, {
        preserveScroll: true,
    })
}
</script>
<template>
    <AdminLayout>

        <Head title="Pending User Approvals" />
        <Link :href="route('admin.dashboard')" as="a"
            class="text-sm text-indigo-600 hover:underline mb-4 inline-block cursor-pointer">
        ← Back to Dashboard
        </Link>
        <div class="p-6 max-w-4xl mx-auto">
            <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white">Pending User Approvals</h1>
            <div v-if="pendingUsers.length === 0" class="text-gray-600 dark:text-gray-400">
                No pending users to review.
            </div>
            <div v-for="user in pendingUsers" :key="user.id" class="border p-4 rounded mb-4 bg-white dark:bg-gray-700">
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.name }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ user.email }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                    Roles: {{ Array.isArray(user.roles) ? user.roles.join(', ') : 'N/A' }}
                </p>
                <video v-if="user.demo_url.endsWith('.mp4') || user.demo_url.endsWith('.mov')"
                    class="w-full mt-2 rounded" style="height: 300px; object-fit: cover;" controls>
                    <source :src="user.demo_url" />
                    Your browser does not support the video tag.
                </video>
                <audio v-else class="w-full mt-2" controls>
                    <source :src="user.demo_url" />
                    Your browser does not support the audio element.
                </audio>
                <div class="mt-4 flex gap-2">
                    <button @click="approveUser(user.id)"
                        class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                        Approve
                    </button>
                    <button @click="rejectUser(user.id)"
                        class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                        Reject
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import TextInput from '@/Components/TextInput.vue'
import { ref, watch } from 'vue'
import { Link } from '@inertiajs/vue3';
const props = defineProps({
    users: Object,
    filters: Object
})
const search = ref(props.filters.search || '')
watch(search, (value) => {
    router.get(route('admin.users.list'), { search: value }, {
        preserveState: true,
        replace: true
    })
})
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('admin.users.destroy', id))
    }
}
</script>
<template>
    <AdminLayout>

        <Head title="User List" />
        <Link :href="route('admin.dashboard')" as="a"
            class="text-sm text-indigo-600 hover:underline mb-4 inline-block cursor-pointer">
        ← Back to Dashboard
        </Link>
        <div class="max-w-6xl mx-auto p-6">
            <h1 class="text-2xl font-bold mb-4 dark:text-white">All Users</h1>
            <TextInput v-model="search" placeholder="Search by name or email..." class="mb-4 w-full" />
            <table class="w-full text-sm border-collapse border dark:border-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-800 dark:text-white">
                    <tr>
                        <th class="p-2 border">Name</th>
                        <th class="p-2 border">Email</th>
                        <th class="p-2 border">Status</th>
                        <th class="p-2 border">Events Created</th>
                        <th class="p-2 border">Events Joined</th>
                        <th class="p-2 border">Followers</th>
                        <th class="p-2 border">Followings</th>
                        <th class="p-2 border">Registered</th>
                        <th class="p-2 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users.data" :key="user.id"
                        class="hover:bg-gray-50 dark:hover:bg-gray-900 dark:text-white">
                        <td class="p-2 border">{{ user.name }}</td>
                        <td class="p-2 border">{{ user.email }}</td>
                        <td class="p-2 border capitalize">{{ user.status }}</td>
                        <td class="p-2 border text-center">{{ user.events_created_count }}</td>
                        <td class="p-2 border text-center">{{ user.events_joined_count }}</td>
                        <td class="p-2 border text-center">{{ user.followers_count }}</td>
                        <td class="p-2 border text-center">{{ user.followings_count }}</td>
                        <td class="p-2 border">{{ new Date(user.created_at).toLocaleDateString() }}</td>
                        <td class="p-2 border text-center">
                            <button @click="confirmDelete(user.id)" class="text-red-600 hover:underline">
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

<script setup>
import { router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { onMounted } from 'vue'
defineProps({
    pendingApplications: Array,
    approvedParticipations: Array
})
function cancelApplication(postId) {
    if (confirm('Are you sure you want to cancel this event?')) {
        router.post(route('event.cancel', postId), {}, {
            preserveScroll: true,
        });
    }
}
</script>
<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                My Applications & Participations
            </h2>
        </template>
        <div class="p-4 space-y-4 overflow-auto h-full">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Pending Applications</h3>
                <div v-if="pendingApplications.length === 0" class="text-gray-600 dark:text-gray-300">
                    No pending applications.
                </div>
                <div v-for="event in pendingApplications" :key="event.id"
                    class="bg-white dark:bg-slate-900 rounded shadow p-4">
                    <h4 class="text-lg font-bold text-gray-800 dark:text-white mb-1">
                        <Link :href="route('post.view', event.id)"
                            class="hover:underline hover:text-indigo-600 dark:hover:text-indigo-400">
                        {{ event.title }}
                        </Link>
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <strong>City:</strong> {{ event.city }} |
                        <strong>Date:</strong> {{ new Date(event.date_time).toLocaleString() }}
                    </p>
                    <div class="mb-3">
                        <p class="text-sm text-yellow-600 dark:text-yellow-400">
                            Pending Approval — {{event.applicants.find(u => u.id === $page.props.auth.user.id)?.role}}
                        </p>
                    </div>
                    <button @click="cancelApplication(event.id)"
                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                        Cancel Application
                    </button>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Approved Participations</h3>
                <div v-if="approvedParticipations.length === 0" class="text-gray-600 dark:text-gray-300">
                    You're not participating in any events yet.
                </div>
                <div v-for="event in approvedParticipations" :key="event.id"
                    class="bg-white dark:bg-slate-900 rounded shadow p-4">
                    <h4 class="text-lg font-bold text-gray-800 dark:text-white mb-1">
                        <Link :href="route('post.view', event.id)"
                            class="hover:underline hover:text-indigo-600 dark:hover:text-indigo-400">
                        {{ event.title }}
                        </Link>
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <strong>City:</strong> {{ event.city }} |
                        <strong>Date:</strong> {{ new Date(event.date_time).toLocaleString() }}
                    </p>
                    <div class="mb-3">
                        <strong class="dark:text-white">Participants:</strong>
                        <ul class="list-disc list-inside ml-2 text-sm text-green-700 dark:text-green-400">
                            <li v-for="user in event.participants" :key="user.id">
                                {{ user.name }} — {{ user.role }}
                                <Link :href="route('profile', { username: user.username })"
                                    class="ml-2 text-indigo-600 dark:text-indigo-400 hover:underline text-xs">
                                View Profile
                                </Link>
                            </li>
                        </ul>
                    </div>
                    <button @click="cancelApplication(event.id)"
                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                        Cancel Participation
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

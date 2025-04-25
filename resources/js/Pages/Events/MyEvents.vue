<script setup>
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3'
const props = defineProps({
    events: Array
});
function approve(postUserId) {
    if (confirm('Are you sure you want to approve this artist?')) {
        router.post(route('event.applicant.approve', postUserId), {}, {
            preserveScroll: true,
        });
    }
}
function reject(postUserId) {
    if (confirm('Are you sure you want to reject this artist?')) {
        router.post(route('event.applicant.reject', postUserId), {}, {
            preserveScroll: true,
        });
    }
}
function removeParticipant(postUserId) {
    if (!confirm('Remove this participant from your event?')) return
    router.post(
        route('event.participant.remove', { postUserId }),
        {},
        { preserveScroll: true }
    )
}
</script>
<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">My Events</h2>
        </template>
        <div class="p-4 h-full space-y-4 overflow-auto">
            <div v-if="events.length === 0" class="text-gray-600 dark:text-gray-300">
                You haven't created any events yet.
            </div>
            <div v-for="event in events" :key="event.id" class="bg-white dark:bg-slate-900 rounded shadow p-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                    <Link :href="route('post.view', event.id)"
                        class="hover:underline hover:text-indigo-600 dark:hover:text-indigo-400">
                    {{ event.title }}
                    </Link>
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                    <strong>City:</strong> {{ event.city }} |
                    <strong>Date:</strong> {{ new Date(event.date_time).toLocaleString() }}
                </p>
                <div class="mb-3">
                    <strong class="dark:text-white">Roles Needed:</strong>
                    <ul class="list-disc list-inside ml-2 text-sm text-gray-600 dark:text-gray-300">
                        <li v-for="role in event.roles" :key="role.name">
                            {{ role.name }} ({{ role.quantity }})
                        </li>
                    </ul>
                </div>
                <div class="mb-3">
                    <strong class="dark:text-white">Participants:</strong>
                    <div v-if="event.participants?.length">
                        <ul class="space-y-2 mt-2">
                            <li v-for="user in event.participants" :key="user.id"
                                class="flex justify-between items-center text-sm bg-gray-100 dark:bg-slate-800 p-2 rounded">
                                <div class="text-green-700 dark:text-green-400">
                                    {{ user.name }} — {{ user.role }}
                                </div>
                                <button @click="removeParticipant(user.request_id)"
                                    class="text-xs text-red-600 hover:underline">
                                    Remove
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div v-else class="text-sm text-gray-600 dark:text-gray-400 ml-2 mt-1">
                        No participants yet.
                    </div>
                </div>
                <div>
                    <strong class="dark:text-white">Applicants:</strong>
                    <ul class="space-y-2 mt-2">
                        <li v-for="user in event.applicants" :key="user.id"
                            class="flex justify-between items-center text-sm bg-gray-100 dark:bg-slate-800 p-2 rounded">
                            <div class="text-gray-800 dark:text-white">
                                {{ user.name }} — {{ user.role }}
                            </div>
                            <div class="flex gap-2">
                                <Link :href="route('profile', { username: user.username })"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-sm">
                                View Profile
                                </Link>
                                <button @click="approve(user.request_id)"
                                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                    Approve
                                </button>
                                <button @click="reject(user.request_id)"
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                    Reject
                                </button>
                            </div>
                        </li>
                        <li v-if="event.applicants.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
                            No applicants yet.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

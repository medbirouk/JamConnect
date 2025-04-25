<script setup>
import { Head, router} from '@inertiajs/vue3'
import GroupList from "@/Components/app/GroupList.vue"
import FollowingList from "@/Components/app/FollowingList.vue"
import PostList from "@/Components/app/PostList.vue"
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue"
import CreatePost from "@/Components/app/CreatePost.vue";


defineProps({
    posts: Object,
    groups: Array,
    followings: Array,
    search: String,
    city: String
})

function goToChat(group) {
    router.get(route('chats.mine'), { group: group.id });
}
</script>

<template>
    <Head title="Search Results" />

    <AuthenticatedLayout>
        <div class="grid lg:grid-cols-12 gap-3 p-4 h-full">
            <div class="lg:col-span-3 lg:order-1 h-full overflow-hidden">
                <GroupList :groups="groups" @click="goToChat" />
            </div>
            <div class="lg:col-span-3 lg:order-3 h-full overflow-hidden">
                <FollowingList :users="followings" />
            </div>
            <div class="lg:col-span-6 lg:order-2 h-full overflow-hidden flex flex-col">
                <CreatePost/>
                <div class="mb-3 text-sm text-gray-600 dark:text-gray-300 px-2">
                    <
                    <span v-if="search">Results for <strong>"{{ search }}"</strong></span>
                    <span v-if="city"> in <strong>{{ city }}</strong></span>
                </div>

                <PostList :posts="posts" class="flex-1" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

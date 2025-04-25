<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ChatBox from '@/Components/app/ChatBox.vue';
import GroupList from '@/Components/app/GroupList.vue';
import ParticipantList from '@/Components/app/ParticipantList.vue'
import { ref } from 'vue';
const props = defineProps({
  groups: Array,
  followings: Array,
  authUserId: Number,
  selectedGroupId: [Number, String, null]
});
const findGroup = id => props.groups.find(g => String(g.id) === String(id))
const selectedGroup = ref(
  findGroup(props.selectedGroupId) ||
  (props.groups.length ? props.groups[0] : null)
)
function selectGroup(group) {
  selectedGroup.value = group;
}
</script>
<template>
  <AuthenticatedLayout>
    <div class="grid lg:grid-cols-12 gap-3 p-4 h-full">
      <div class="lg:col-span-3 lg:order-1 h-full overflow-hidden">
        <GroupList :groups="groups" @click="selectGroup" />
      </div>
      <div v-if="selectedGroup" class="lg:col-span-3 lg:order-3 h-full overflow-hidden">
        <ParticipantList :users="selectedGroup.users" />
      </div>
      <div class="lg:col-span-6 lg:order-2 h-full overflow-hidden">
        <div v-if="selectedGroup" class="h-full">
          <ChatBox :group="selectedGroup" :current-user-id="authUserId" />
        </div>
        <div v-else class="text-gray-500 dark:text-gray-300 text-center mt-10">
          Select a group to start chatting.
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import TextInput from '@/Components/TextInput.vue'
import UserListItem from '@/Components/app/UserListItem.vue'
import { ref, computed } from 'vue'
const props = defineProps({
  users: Array
})
const searchKeyword = ref('')
const filteredUsers = computed(() => {
  const kw = searchKeyword.value.toLowerCase().trim()
  if (!kw) return props.users
  return props.users.filter(u =>
    u.name.toLowerCase().includes(kw)
  )
})
</script>
<template>
  <TextInput v-model="searchKeyword" placeholder="Type to search" class="w-full mt-3" />
  <div class="mt-3 h-[200px] lg:flex-1 overflow-auto">
    <div v-if="!filteredUsers.length" class="text-gray-400 text-center p-3">
      You don't have friends yet.
    </div>
    <div v-else class="space-y-2">
      <UserListItem v-for="user in filteredUsers" :key="user.id" :user="user" class="rounded-lg" />
    </div>
  </div>
</template>
<style scoped></style>
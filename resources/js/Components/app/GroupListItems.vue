<script setup>
import TextInput from '@/Components/TextInput.vue'
import { ref, computed } from 'vue'
const props = defineProps({
  groups: Array
})
const emit = defineEmits(['click'])
const search = ref('')
const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return props.groups
  return props.groups.filter(g =>
    g.post.title.toLowerCase().includes(q)
  )
})
</script>
<template>
  <TextInput v-model="search" placeholder="Type to search" class="w-full mt-3" />
  <div class="mt-3 space-y-2 max-h-[200px] overflow-auto">
    <div v-if="!filtered.length" class="text-gray-400 text-center p-3">
      No groups yet.
    </div>
    <div v-else class="space-y-2">
      <div v-for="grp in filtered" :key="grp.id" @click="$emit('click', grp)" class="
          bg-white dark:bg-slate-900 dark:text-gray-100
          transition-all
          border-2 border-transparent hover:border-indigo-500
          cursor-pointer
          rounded-lg
        ">
        <div class="flex items-center gap-2 py-2 px-2">
          <img src="/img/GroupLogo1.jpg" alt="Group avatar" class="w-8 h-8 rounded-full object-cover" />
          <div class="flex-1">
            <h3 class="font-black hover:underline text-sm text-gray-800 dark:text-white">
              {{ grp.post.title }}
            </h3>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped></style>
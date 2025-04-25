<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import GroupListItems from '@/Components/app/GroupListItems.vue'
defineProps({ groups: Array })
const emit = defineEmits(['click'])
</script>
<template>
  <div
    class="px-3 bg-white dark:bg-slate-950 rounded border dark:border-slate-900 dark:text-gray-100 h-full py-3 overflow-hidden">
    <div class="block lg:hidden">
      <Disclosure v-slot="{ open }">
        <DisclosureButton class="w-full flex justify-between items-center">
          <h2 class="text-xl font-bold">My Group Chats</h2>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="w-6 h-6 transition-all" :class="open ? 'rotate-90 transform' : ''">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
          </svg>
        </DisclosureButton>
        <DisclosurePanel class="pt-2">
          <GroupListItems :groups="groups" @click="emit('click', $event)" />
        </DisclosurePanel>
      </Disclosure>
    </div>
    <div class="hidden lg:flex lg:flex-col h-full">
      <h2 class="text-xl font-bold">My Group Chats</h2>
      <GroupListItems class="mt-3 flex-1" :groups="groups" @click="emit('click', $event)" />
    </div>
  </div>
</template>
<style scoped></style>
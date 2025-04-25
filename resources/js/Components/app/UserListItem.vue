<script setup>
import { Link } from '@inertiajs/vue3'
defineProps({
  user: {
    type: Object,
    required: true
  }
})
</script>
<template>
  <Link :href="route('profile', user.username)" class="
      block
      bg-white dark:bg-slate-900 dark:text-gray-100
      transition-all
      border-2 border-transparent
      hover:border-indigo-500
      cursor-pointer
      rounded-lg
    ">
  <div class="flex items-center gap-2 p-2">
    <img :src="user.avatar_url" alt="Avatar" class="w-8 h-8 rounded-full object-cover" />
    <h3 class="font-black text-sm text-gray-800 dark:text-white">
      {{ user.name }}
    </h3>
  </div>
  </Link>
</template>
<style scoped></style>
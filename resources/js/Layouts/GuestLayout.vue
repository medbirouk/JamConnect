<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import { Link } from '@inertiajs/vue3'
</script>
<template>
  <div class="min-h-screen flex flex-col lg:flex-row bg-slate-900 text-white">
    <div class="flex-1 lg:w-1/2 flex items-center justify-center p-8">
      <Link href="/" class="w-full">
      <ApplicationLogo class="h-100" />
      </Link>
    </div>
    <div class="flex-1 lg:w-1/2 flex items-start lg:items-center justify-center p-8">
      <div class="w-full sm:max-w-md bg-white dark:bg-gray-800
               shadow-md sm:rounded-lg p-6
               max-h-[90vh] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-500/30">
        <slot />
      </div>
    </div>
  </div>
</template>
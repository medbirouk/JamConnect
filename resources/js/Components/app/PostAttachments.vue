<script setup>
import { ArrowDownTrayIcon } from '@heroicons/vue/24/outline'
import { PaperClipIcon } from '@heroicons/vue/24/solid'
import { isImage, isVideo } from '@/helpers.js'
defineProps({ attachments: Array })
defineEmits(['attachmentClick'])
</script>
<template>
  <template v-for="(a, i) in attachments.slice(0, 4)" :key="a.id ?? i">
    <div @click="$emit('attachmentClick', i)" class="group aspect-square bg-blue-100 flex items-center justify-center
                text-gray-500 relative cursor-pointer overflow-hidden">
      <div v-if="i === 3 && attachments.length > 4"
        class="absolute inset-0 z-10 bg-black/60 text-white flex items-center justify-center text-2xl">
        +{{ attachments.length - 4 }}&nbsp;more
      </div>
      <a @click.stop :href="route('post.download', a)" class="z-20 opacity-0 group-hover:opacity-100 transition w-8 h-8 flex items-center
                justify-center text-gray-100 bg-gray-700 rounded absolute right-2 top-2 hover:bg-gray-800">
        <ArrowDownTrayIcon class="w-4 h-4" />
      </a>
      <video v-if="isVideo(a)" :src="a.url" controls playsinline class="object-cover w-full h-full" />
      <img v-else-if="isImage(a)" :src="a.url" class="object-cover w-full h-full" />
      <div v-else class="flex flex-col items-center">
        <PaperClipIcon class="w-10 h-10 mb-3" />
        <small>{{ a.name }}</small>
      </div>
    </div>
  </template>
</template>
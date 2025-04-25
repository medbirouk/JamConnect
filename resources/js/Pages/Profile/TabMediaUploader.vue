<script setup>
import { ref } from 'vue'
import axios from '@/axiosClient'
import { ArrowDownTrayIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { isImage, isVideo } from '@/helpers.js'
const props = defineProps({
  media: Array,
  mine: Boolean
})
const emit = defineEmits(['uploaded', 'deleted'])
async function onFile(e) {
  const file = e.target.files[0]
  if (!file) return
  const form = new FormData()
  form.append('file', file)
  const { data } = await axios.post(route('profile.media.store'), form, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })
  emit('uploaded', data)
}
async function remove(item) {
  if (!confirm('Delete this media from your profile?')) return
  await axios.delete(route('profile.media.destroy', item.id))
  emit('deleted', item.id)
}
</script>
<template>
  <div class="mb-3" v-if="mine">
    <label class="block w-full p-4 text-center border-2 border-dashed rounded cursor-pointer
                  hover:bg-gray-50 dark:hover:bg-slate-800">
      <span class="text-sm text-gray-600 dark:text-gray-200">Click or drop to upload image / video</span>
      <input type="file" accept="image/*,video/*" class="hidden" @change="onFile">
    </label>
  </div>
  <div class="grid gap-2 grid-cols-2 sm:grid-cols-3">
    <div v-for="m in media" :key="m.id" class="group relative aspect-square bg-blue-100 overflow-hidden">
      <video v-if="isVideo(m)" :src="m.url" muted playsinline class="object-cover w-full h-full" />
      <img v-else :src="m.url" class="object-cover w-full h-full" />
      <a :href="route('post.download', m)" class="opacity-0 group-hover:opacity-100 transition absolute right-2 top-2
                w-7 h-7 flex items-center justify-center rounded bg-gray-800/80 text-white">
        <ArrowDownTrayIcon class="w-4 h-4" />
      </a>
      <button v-if="mine" @click="remove(m)" class="opacity-0 group-hover:opacity-100 transition absolute left-2 top-2
                     w-7 h-7 flex items-center justify-center rounded bg-red-600/80 text-white">
        <TrashIcon class="w-4 h-4" />
      </button>
    </div>
  </div>
  <p v-if="!media?.length" class="py-8 text-center text-gray-600 dark:text-gray-100">No media yet.</p>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from '@/axiosClient'
import { isImage } from '@/helpers.js'
import { ArrowDownTrayIcon, TrashIcon } from '@heroicons/vue/24/outline'
import AttachmentPreviewModal from '@/Components/app/AttachmentPreviewModal.vue'
const props = defineProps({
  photos: Array,
  isMyProfile: Boolean
})
const media = ref([...props.photos])
const images = computed(() => media.value.filter(isImage))
async function onFile(e) {
  const file = e.target.files[0]
  if (!file) return
  const fd = new FormData()
  fd.append('file', file)
  const { data } = await axios.post(
    route('profile.media.store'), fd,
    { headers: { 'Content-Type': 'multipart/form-data' } })
  media.value.unshift(data)
}
async function remove(item) {
  if (!confirm('Delete this photo from your profile?')) return
  await axios.delete(route('profile.media.destroy', item.id))
  media.value = media.value.filter(m => m.id !== item.id)
}
const current = ref(0)
const show = ref(false)
function open(i) { current.value = i; show.value = true }
</script>
<template>
  <div class="mb-3" v-if="isMyProfile">
    <label class="block w-full p-4 text-center border-2 border-dashed rounded cursor-pointer
                   hover:bg-gray-50 dark:hover:bg-slate-800">
      <span class="text-sm text-gray-600 dark:text-gray-200">
        Click or drop to upload image
      </span>
      <input type="file" accept="image/*" class="hidden" @change="onFile">
    </label>
  </div>
  <div class="grid gap-2 grid-cols-2 sm:grid-cols-3">
    <template v-for="(att, i) in images" :key="att.id">
      <div @click="open(i)" class="group relative aspect-square bg-blue-100 overflow-hidden cursor-pointer">
        <img v-if="isImage(att)" :src="att.url" class="object-cover w-full h-full" />
        <a :href="route('post.download', att)" @click.stop class="opacity-0 group-hover:opacity-100 transition absolute right-2 top-2
                  w-7 h-7 flex items-center justify-center rounded bg-gray-800/80 text-white">
          <ArrowDownTrayIcon class="w-4 h-4" />
        </a>
        <button v-if="isMyProfile" @click.stop="remove(att)" class="opacity-0 group-hover:opacity-100 transition absolute left-2 top-2
                       w-7 h-7 flex items-center justify-center rounded bg-red-600/80 text-white">
          <TrashIcon class="w-4 h-4" />
        </button>
      </div>
    </template>
  </div>
  <p v-if="!images.length" class="py-8 text-center text-gray-600 dark:text-gray-100">
    No photos yet.
  </p>
  <AttachmentPreviewModal :attachments="images" v-model:index="current" v-model="show" />
</template>
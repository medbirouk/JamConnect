<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axiosClient from '@/axiosClient.js'
import PostItem from '@/Components/app/PostItem.vue'
import PostModal from '@/Components/app/PostModal.vue'
import AttachmentPreviewModal from '@/Components/app/AttachmentPreviewModal.vue'
const props = defineProps({
  posts: { type: Object, required: true }
})
const page = usePage()
const authUser = page.props.auth.user
const showEditModal = ref(false)
const showAttachmentsModal = ref(false)
const editPost = ref({})
const previewAttachmentsPost = ref({})
const loadMoreIntersect = ref(null)
const allPosts = ref({ data: [], next: null })
watch(
  () => props.posts,
  (postsData) => {
    if (postsData && Array.isArray(postsData.data)) {
      allPosts.value = {
        data: postsData.data,
        next: postsData.links?.next ?? null
      }
    }
  },
  { immediate: true }
)
function openEditModal(post) {
  editPost.value = post
  showEditModal.value = true
}
function openAttachmentPreviewModal(post, index) {
  previewAttachmentsPost.value = { post, index }
  showAttachmentsModal.value = true
}
function onModalHide() {
  editPost.value = { id: null, body: '', user: authUser }
}
function loadMore() {
  if (!allPosts.value.next) return
  axiosClient
    .get(allPosts.value.next, { headers: { Accept: 'application/json' } })
    .then(({ data: newPosts }) => {
      if (newPosts?.data && Array.isArray(newPosts.data)) {
        allPosts.value.data.push(...newPosts.data)
        allPosts.value.next = newPosts.links?.next ?? null
      } else {
        console.warn('Unexpected pagination response:', newPosts)
      }
    })
    .catch((e) => console.error('Pagination load failed:', e))
}
let observer
onMounted(() => {
  observer = new IntersectionObserver(
    (entries) =>
      entries.forEach((entry) => entry.isIntersecting && loadMore()),
    { rootMargin: '-250px 0px 0px 0px' }
  )
  if (loadMoreIntersect.value) observer.observe(loadMoreIntersect.value)
})
onBeforeUnmount(() => {
  observer?.disconnect()
})
</script>
<template>
  <div class="overflow-auto">
    <PostItem v-for="post in allPosts.data" :key="post.id" :post="post" @editClick="openEditModal"
      @attachmentClick="openAttachmentPreviewModal" />
    <div ref="loadMoreIntersect"></div>
    <PostModal :post="editPost" v-model="showEditModal" @hide="onModalHide" />
    <AttachmentPreviewModal :attachments="previewAttachmentsPost.post?.attachments || []"
      v-model:index="previewAttachmentsPost.index" v-model="showAttachmentsModal" />
  </div>
</template>
<style scoped></style>
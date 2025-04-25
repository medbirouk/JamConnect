<script setup>
import { ref, nextTick, reactive, onUnmounted, computed, watch } from 'vue'
import axios from 'axios'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { EllipsisVerticalIcon, PencilIcon, TrashIcon, MapPinIcon } from '@heroicons/vue/24/outline'
const props = defineProps({
    group: Object,
    currentUserId: Number
})
const messages = ref([])
const newMessage = ref('')
const editingId = ref(null)
const editingText = ref('')
const pinnedMessage = ref(props.group.pinned_message)
const msgEls = reactive({})
let channel = null
const isEventOwner = computed(() => props.group.post.user_id === props.currentUserId)
const canManagePinned = computed(() =>
    pinnedMessage.value &&
    (isEventOwner.value || pinnedMessage.value.user_id === props.currentUserId)
)
const container = () => document.getElementById('msg-container')
function autoResize(el) { el.style.height = 'auto'; el.style.height = el.scrollHeight + 'px' }
function scrollToBottom() { nextTick(() => { const c = container(); if (c) c.scrollTop = c.scrollHeight }) }
function formatTime(ts) {
    if (!ts) return ''
    const d = new Date(ts)
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
function escapeHtml(s) {
    return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
}
function linkify(s) {
    const esc = escapeHtml(s)
    return esc.replace(/(https?:\/\/[^\s]+)/g, url =>
        `<a href="${url}" target="_blank" rel="noopener noreferrer"
            class="underline text-indigo-200 hover:text-indigo-100">${url}</a>`
    )
}

watch(() => props.group, async (newGroup, oldGroup) => {
    if (oldGroup) {
        
        window.Echo.leave(`group.${oldGroup.id}`)
    }
    messages.value = []
    pinnedMessage.value = newGroup.pinned_message
    // 👂 Listen to new group
    channel = window.Echo.channel(`group.${newGroup.id}`)
        .listen('.message.sent', e => { messages.value.push(e.message); scrollToBottom() })
        .listen('.message.updated', e => {
            const i = messages.value.findIndex(m => m.id === e.message.id)
            if (i > -1) messages.value[i] = e.message
        })
        .listen('.message.deleted', e => {
            const i = messages.value.findIndex(m => m.id === e.id)
            if (i > -1) messages.value.splice(i, 1)
            if (pinnedMessage.value?.id === e.id) pinnedMessage.value = null
        })
        .listen('.message.pinned', e => pinnedMessage.value = e.message)
    await fetchMessages()
}, { immediate: true }) 
onUnmounted(() => {
    if (props.group?.id) window.Echo.leave(`group.${props.group.id}`)
})
async function fetchMessages() {
    try {
        const r = await axios.get(`/groups/${props.group.id}/messages`)
        messages.value = r.data
        scrollToBottom()
    } catch (e) {
        console.error('Failed to fetch messages', e)
    }
}
function sendMessage() {
    const text = newMessage.value.trim()
    if (!text) return
    /* optimistic bubble */
    const tmpId = `tmp-${Date.now()}`
    messages.value.push({
        id: tmpId,
        message: text,
        created_at: new Date().toISOString(), 
        user_id: props.currentUserId,
        user: { name: 'You', avatar_path: null }
    })
    scrollToBottom()
    axios.post(`/groups/${props.group.id}/messages`,
        { message: text },
        { headers: { 'X-Socket-Id': window.Echo.connector.pusher.connection.socket_id } }
    ).then(({ data }) => {
        
        const idx = messages.value.findIndex(m => m.id === tmpId)
        if (idx > -1) messages.value[idx] = data.message
        else messages.value.push(data.message)        
        newMessage.value = ''
    }).catch(() => {
        messages.value = messages.value.filter(m => m.id !== tmpId)
    })
}
function composerKeydown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault(); sendMessage()
    }
}
function startEdit(m) {
    editingId.value = m.id
    editingText.value = m.message
    nextTick(() => {
        const el = msgEls[m.id]
        if (el) el.scrollIntoView({ block: 'center', behavior: 'smooth' })
    })
}
function cancelEdit() { editingId.value = null }
function saveEdit(m) {
    axios.put(`/groups/${props.group.id}/messages/${m.id}`, { message: editingText.value })
        .then(() => { m.message = editingText.value; editingId.value = null })
}
function editKeydown(m, e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault(); saveEdit(m)
    }
}
function deleteMsg(m) {
    if (!confirm('Delete this message?')) return
    axios.delete(`/groups/${props.group.id}/messages/${m.id}`)
        .then(() => {
            messages.value = messages.value.filter(x => x !== m)
            if (pinnedMessage.value?.id === m.id) pinnedMessage.value = null
        })
}
function togglePin(m) {
    axios.post(`/groups/${props.group.id}/messages/${m.id}/pin`, { message_id: m.id })
        .then(r => pinnedMessage.value = r.data.message ?? null)
}
</script>
<template>
    <div class="h-full flex flex-col border rounded-md overflow-hidden
              bg-white dark:bg-slate-900">
        
        <div class="px-4 py-3 border-b dark:border-slate-700 space-y-2">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                {{ group.post.title }} Chat
            </h2>
           
            <div v-if="pinnedMessage" class="flex items-start gap-2">
                <MapPinIcon class="w-4 h-4 text-indigo-500 mt-1" />
                <div class="flex-1 bg-indigo-50 dark:bg-indigo-900/40
                    text-indigo-900 dark:text-indigo-100
                    px-3 py-1 rounded shadow text-sm whitespace-pre-line" v-html="linkify(pinnedMessage.message)" />
                <Menu v-if="canManagePinned" as="div" class="relative">
                    <MenuButton class="w-6 h-6 rounded-full flex items-center justify-center
                             text-indigo-800 dark:text-indigo-100
                             hover:bg-indigo-200/40 dark:hover:bg-indigo-800/40">
                        <EllipsisVerticalIcon class="w-4 h-4" />
                    </MenuButton>
                    <transition enter-active-class="transition duration-100 ease-out"
                        enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
                        leave-active-class="transition duration-75 ease-in"
                        leave-from-class="transform scale-100 opacity-100"
                        leave-to-class="transform scale-95 opacity-0">
                        <MenuItems class="absolute right-0 mt-2 w-32 origin-top-right rounded-md
                               bg-slate-800 text-white shadow-lg ring-1 ring-black/10
                               focus:outline-none z-50">
                            <div class="px-1 py-1 space-y-1">
                                <MenuItem v-slot="{ active }">
                                <button @click="startEdit(pinnedMessage)" :class="[active ? 'bg-slate-700' : '',
                                    'flex w-full items-center gap-2 rounded px-2 py-1 text-sm']">
                                    <PencilIcon class="w-4 h-4" /> Edit
                                </button>
                                </MenuItem>
                                <MenuItem v-slot="{ active }">
                                <button @click="deleteMsg(pinnedMessage)" :class="[active ? 'bg-slate-700' : '',
                                    'flex w-full items-center gap-2 rounded px-2 py-1 text-sm']">
                                    <TrashIcon class="w-4 h-4" /> Delete
                                </button>
                                </MenuItem>
                                <MenuItem v-slot="{ active }">
                                <button @click="togglePin(pinnedMessage)" :class="[active ? 'bg-slate-700' : '',
                                    'flex w-full items-center gap-2 rounded px-2 py-1 text-sm']">
                                    <MapPinIcon class="w-4 h-4" /> Un‑pin
                                </button>
                                </MenuItem>
                            </div>
                        </MenuItems>
                    </transition>
                </Menu>
            </div>
        </div>
        
        <div id="msg-container" class="flex-1 px-4 py-2 overflow-y-auto space-y-2
                bg-gray-50 dark:bg-slate-800">
            <div v-for="msg in messages" :key="msg.id" :ref="el => msgEls[msg.id] = el" :class="['flex items-start gap-2',
                { 'justify-end': msg.user_id === currentUserId }]">
              
                <template v-if="msg.user_id !== currentUserId">
                    <div class="flex items-start space-x-2">
                        <img :src="msg.user.avatar_path
                            ? '/storage/' + msg.user.avatar_path
                            : '/img/default_avatar.webp'" class="w-5 h-5 rounded-full object-cover" />
                        
                        <div class="flex flex-col">
                            <div class="bg-white dark:bg-slate-700 rounded-lg px-3 py-2 shadow max-w-xs">
                               
                                <div class="text-xs font-semibold text-green-600">
                                    {{ msg.user.name }}
                                </div>
                                
                                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line"
                                    v-html="linkify(msg.message)" />
                                
                                <div class="mt-1 text-[8px] text-gray-500 dark:text-gray-400 text-right">
                                    {{ formatTime(msg.created_at) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
              
                <template v-else>
                    <div class="relative bg-indigo-500 text-white px-3 pr-10 py-1 rounded shadow text-sm
                      max-w-[75%] whitespace-pre-line flex flex-col items-end">
                       
                        <template v-if="editingId === msg.id">
                            <textarea rows="1" v-model="editingText" @input="autoResize($event.target)"
                                @keydown="e => editKeydown(msg, e)" :ref="el => el && autoResize(el)"
                                class="w-full resize-none bg-transparent outline-none scrollbar-hide" />
                            <div class="flex justify-end gap-2 text-xs mt-1">
                                <button @click="saveEdit(msg)" class="hover:underline">save</button>
                                <button @click="cancelEdit" class="hover:underline">cancel</button>
                            </div>
                        </template>
                       
                        <template v-else>
                            <div v-html="linkify(msg.message)" />
                        </template>
                        
                        <span class="bottom-1 right-2 text-[8px] opacity-75 w-full text-right">
                            {{ formatTime(msg.created_at) }}
                        </span>
                        
                        <Menu as="div" class="absolute top-0 right-0 z-40" @open="$nextTick(() => {
                            const c = container(),
                                r = $el.lastElementChild.getBoundingClientRect(),
                                diff = r.bottom - c.getBoundingClientRect().bottom;
                            if (diff > 0) c.scrollTop += diff + 8
                        })">
                            <MenuButton class="mt-1 mr-1 w-5 h-5 rounded-full flex items-center justify-center
                                 text-white/70 hover:text-white focus:outline-none">
                                <EllipsisVerticalIcon class="w-4 h-4" />
                            </MenuButton>
                            <transition enter-active-class="transition duration-100 ease-out"
                                enter-from-class="transform scale-95 opacity-0"
                                enter-to-class="transform scale-100 opacity-100"
                                leave-active-class="transition duration-75 ease-in"
                                leave-from-class="transform scale-100 opacity-100"
                                leave-to-class="transform scale-95 opacity-0">
                                <MenuItems class="absolute right-4 mt-2 w-32 origin-top-right rounded-md
                                   bg-slate-800 text-white shadow-lg ring-1 ring-black/10
                                   focus:outline-none z-50">
                                    <div class="px-1 py-1 space-y-1">
                                        <MenuItem v-slot="{ active }">
                                        <button @click="startEdit(msg)" :class="[active ? 'bg-slate-700' : '',
                                            'flex w-full items-center gap-2 rounded px-2 py-1 text-sm']">
                                            <PencilIcon class="w-4 h-4" /> Edit
                                        </button>
                                        </MenuItem>
                                        <MenuItem v-slot="{ active }">
                                        <button @click="deleteMsg(msg)" :class="[active ? 'bg-slate-700' : '',
                                            'flex w-full items-center gap-2 rounded px-2 py-1 text-sm']">
                                            <TrashIcon class="w-4 h-4" /> Delete
                                        </button>
                                        </MenuItem>
                                        <MenuItem v-if="isEventOwner" v-slot="{ active }">
                                        <button @click="togglePin(msg)" :class="[active ? 'bg-slate-700' : '',
                                            'flex w-full items-center gap-2 rounded px-2 py-1 text-sm']">
                                            <MapPinIcon class="w-4 h-4" />
                                            {{ pinnedMessage?.id === msg.id ? 'Un‑pin' : 'Pin' }}
                                        </button>
                                        </MenuItem>
                                    </div>
                                </MenuItems>
                            </transition>
                        </Menu>
                    </div>
                </template>
            </div>
        </div>
        
        <div class="border-t px-4 py-2 dark:border-slate-700">
            <div class="flex items-center gap-2">
                <textarea rows="1" v-model="newMessage" @input="autoResize($event.target)" @keydown="composerKeydown"
                    placeholder="Type your message…  (Shift+Enter = newline)" class="flex-1 px-3 py-2 rounded border resize-none
                         dark:bg-slate-800 dark:text-white dark:border-slate-600
                         focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                <button @click="sendMessage"
                    class="px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600 transition">
                    Send
                </button>
            </div>
        </div>
    </div>
</template>
<style scoped>
::-webkit-scrollbar {
    width: 6px
}

::-webkit-scrollbar-thumb {
    background-color: rgba(100, 100, 100, .4);
    border-radius: 3px
}

.scrollbar-hide::-webkit-scrollbar {
    display: none
}
</style>

<script setup>
import { computed, ref, watch } from 'vue'
import { XMarkIcon, PaperClipIcon, BookmarkIcon, ArrowUturnLeftIcon } from '@heroicons/vue/24/solid'
import PostUserHeader from "@/Components/app/PostUserHeader.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import { isImage, isVideo } from "@/helpers.js";
import axiosClient from "@/axiosClient.js";
import UrlPreview from "@/Components/app/UrlPreview.vue";
import BaseModal from "@/Components/app/BaseModal.vue";
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import CityAutocomplete from '@/Components/app/CityAutocomplete.vue'
import { availableRoles } from '@/data/roles'


const editor = ClassicEditor;
const editorConfig = {
    mediaEmbed: {
        removeProviders: ['dailymotion', 'spotify', 'youtube', 'vimeo', 'instagram', 'twitter', 'googleMaps', 'flickr', 'facebook']
    },
    toolbar: ['bold', 'italic', '|', 'bulletedList', 'numberedList', '|', 'heading', '|', 'outdent', 'indent', '|', 'link', '|', 'blockQuote'],
}

const props = defineProps({
    post: {
        type: Object,
        required: true
    },
    group: {
        type: Object,
        default: null
    },
    modelValue: Boolean
})

const attachmentExtensions = usePage().props.attachmentExtensions;
/**
 * {
 *     file: File,
 *     url: '',
 * }
 * @type {Ref<UnwrapRef<*[]>>}
 */
const attachmentFiles = ref([])
const attachmentErrors = ref([])
const formErrors = ref({});


const form = useForm({
    title: '',
    city: '',
    date: '',
    time: '',
    roles: [],
    body: '',
    group_id: null,
    attachments: [],
    deleted_file_ids: [],
    preview: {},
    preview_url: null,
    _method: 'POST'
});

const show = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const computedAttachments = computed(() => {
    return [...attachmentFiles.value, ...(props.post.attachments || [])]
})
const showExtensionsText = computed(() => {
    for (let myFile of attachmentFiles.value) {
        const file = myFile.file
        let parts = file.name.split('.')
        let ext = parts.pop().toLowerCase()
        if (!attachmentExtensions.includes(ext)) {
            return true
        }
    }

    return false;
})


const selectedRoleNames = ref([])

const roleQuantities = ref({}) 

function addRoleName(roleName) {
    if (!selectedRoleNames.value.includes(roleName)) {
        selectedRoleNames.value.push(roleName)
        if (!roleQuantities.value[roleName]) {
            roleQuantities.value[roleName] = 1
        }
    }
}

function removeRoleName(roleName) {
    selectedRoleNames.value = selectedRoleNames.value.filter(r => r !== roleName)
    delete roleQuantities.value[roleName]
}




const emit = defineEmits(['update:modelValue', 'hide'])

watch(() => props.post, () => {
    form.body = props.post.body || ''
    form.title = props.post.title || ''
    form.city = props.post.city || ''
    form.preview = props.post.preview || {}
    form.preview_url = props.post.preview_url || ''

    if (props.post.date_time) {
        const dt = new Date(props.post.date_time)
        form.date = dt.toISOString().slice(0, 10)
        form.time = dt.toTimeString().slice(0, 5) 
    }

    
    selectedRoleNames.value = (props.post.roles || []).map(role => role.name)
    roleQuantities.value = {}
        ; (props.post.roles || []).forEach(role => {
            if (role.name) {
                roleQuantities.value[role.name] = role.quantity || 1
            }
        })

    onInputChange();
})


function closeModal() {
    show.value = false
    emit('hide')
    resetModal();
}

function resetModal() {
    form.reset()
    formErrors.value = {}
    attachmentFiles.value = []
    attachmentErrors.value = [];
    if (props.post.attachments) {
        props.post.attachments.forEach(file => file.deleted = false)
    }
}

function submit() {

    form.roles = selectedRoleNames.value.map(role => ({
        name: role,
        quantity: roleQuantities.value[role] || 1,
    }))


    if (props.group) {
        form.group_id = props.group.id
    }
    form.attachments = attachmentFiles.value.map(myFile => myFile.file)
    if (props.post.id) {
        form._method = 'PUT'
        form.post(route('post.update', props.post.id), {
            preserveScroll: true,
            onSuccess: (res) => {
                closeModal()
            },
            onError: (errors) => {
                processErrors(errors)
            }
        })
    } else {
        form.post(route('post.create'), {
            preserveScroll: true,
            onSuccess: (res) => {
                closeModal()
            },
            onError: (errors) => {
                processErrors(errors)
            }
        })
    }
}

function processErrors(errors) {
    formErrors.value = errors
    for (const key in errors) {
        if (key.includes('.')) {
            const [, index] = key.split('.')
            attachmentErrors.value[index] = errors[key]
        }
    }
}

async function onAttachmentChoose($event) {
    for (const file of $event.target.files) {
        const myFile = {
            file,
            url: await readFile(file)
        }
        attachmentFiles.value.push(myFile)
    }
    $event.target.value = null;
}



async function readFile(file) {
    // use an object‑URL for all previews
    return URL.createObjectURL(file);
}

function removeFile(myFile) {
    if (myFile.file) {
        attachmentFiles.value = attachmentFiles.value.filter(f => f !== myFile)
    } else {
        form.deleted_file_ids.push(myFile.id)
        myFile.deleted = true
    }
}

function undoDelete(myFile) {
    myFile.deleted = false;
    form.deleted_file_ids = form.deleted_file_ids.filter(id => myFile.id !== id)
}



function fetchPreview(url) {
    if (url === form.preview_url) {
        return;
    }

    form.preview_url = url
    form.preview = {}
    if (url) {
        axiosClient.post(route('post.fetchUrlPreview'), { url })
            .then(({ data }) => {
                form.preview = {
                    title: data['og:title'],
                    description: data['og:description'],
                    image: data['og:image']
                }
            })
            .catch(err => {
                console.log(err)
            })
    }
}


function onInputChange() {
    let url = matchHref()

    if (!url) {
        url = matchLink()
    }
    fetchPreview(url)
}

function matchHref() {
    
    const urlRegex = /<a.+href="((https?):\/\/[^"]+)"/;

    
    const match = form.body.match(urlRegex);

    
    if (match && match.length > 0) {
        return match[1];
    }
    return null;
}

function matchLink() {
    
    const urlRegex = /(?:https?):\/\/[^\s<]+/;

    
    const match = form.body.match(urlRegex);

    
    if (match && match.length > 0) {
        return match[0];
    }
    return null
}

</script>

<template>
    <BaseModal :title="post.id ? 'Update Event' : 'Create Event'" v-model="show" @hide="closeModal">
        <div class="p-4">
            <PostUserHeader :post="post" :show-time="false" class="mb-4 dark:text-gray-100" />

            <div v-if="formErrors.group_id" class="bg-red-400 py-2 px-3 rounded text-white mb-3">
                {{ formErrors.group_id }}
            </div>

            <div class="relative group">

               
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Event
                        Title</label>
                    <input id="title" v-model="form.title" type="text"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm"
                        placeholder="e.g. Hip-Hop Night, Jamming Session" />
                    <p v-if="formErrors.title" class="text-sm text-red-500 mt-1">{{ formErrors.title }}</p>
                </div>

                
                <div class="mb-4">
                    <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">City</label>
                    <CityAutocomplete @update:city="form.city = $event" />
                    <p v-if="formErrors.city" class="text-sm text-red-500 mt-1">{{ formErrors.city }}</p>
                </div>

               
                <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Event Date</label>
                        <input type="date" v-model="form.date"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Event Time</label>
                        <input type="time" v-model="form.time"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm" />
                    </div>
                </div>

                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Roles Needed</label>
                    <multiselect v-model="selectedRoleNames" :options="availableRoles.map(r => r.name)" :multiple="true"
                        :taggable="true" placeholder="Select roles" @tag="addRoleName" @remove="removeRoleName"
                        class="mb-2" />

                    <div v-for="(role, index) in selectedRoleNames" :key="role" class="flex items-center gap-3 mb-2">
                        <span class="w-24 text-gray-900 dark:text-gray-100">{{ role }}</span>
                        <input type="number" min="1" v-model.number="roleQuantities[role]"
                            class="w-20 px-2 py-1 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                        <button @click="removeRoleName(role)"
                            class="px-2 py-1 rounded bg-red-600 text-white text-sm hover:bg-red-700 transition">
                            Remove
                        </button>
                    </div>


                    <p class="text-xs text-gray-500 mt-1">Specify the number of participants needed for each role.</p>
                </div>


                <ckeditor :editor="editor" v-model="form.body" :config="editorConfig" @input="onInputChange"></ckeditor>

                <UrlPreview :preview="form.preview" :url="form.preview_url" />


            </div>

            <div v-if="showExtensionsText"
                class="border-l-4 border-amber-500 py-2 px-3 bg-amber-100 mt-3 text-gray-800">
                Files must be one of the following extensions <br>
                <small>{{ attachmentExtensions.join(', ') }}</small>
            </div>

            <div v-if="formErrors.attachments"
                class="border-l-4 border-red-500 py-2 px-3 bg-red-100 mt-3 text-gray-800">
                {{ formErrors.attachments }}
            </div>

            <div class="grid gap-3 my-3" :class="[
                computedAttachments.length === 1 ? 'grid-cols-1' : 'grid-cols-2'
            ]">
                <div v-for="(myFile, ind) of computedAttachments">
                    <div class="group aspect-square bg-blue-100 flex flex-col items-center justify-center text-gray-500 relative border-2"
                        :class="attachmentErrors[ind] ? 'border-red-500' : ''">

                        <div v-if="myFile.deleted"
                            class="absolute z-10 left-0 bottom-0 right-0 py-2 px-3 text-sm bg-black text-white flex justify-between items-center">
                            To be deleted

                            <ArrowUturnLeftIcon @click="undoDelete(myFile)" class="w-4 h-4 cursor-pointer" />
                        </div>

                        <button @click="removeFile(myFile)"
                            class="absolute z-20 right-3 top-3 w-7 h-7 flex items-center justify-center bg-black/30 text-white rounded-full hover:bg-black/40">
                            <XMarkIcon class="h-5 w-5" />
                        </button>

                        <img v-if="isImage(myFile.file || myFile)" :src="myFile.url"
                            class="object-contain aspect-square" :class="myFile.deleted ? 'opacity-50' : ''" />
                        <video v-else-if="isVideo(myFile.file || myFile)" :src="myFile.url" controls playsinline
                            class="object-contain aspect-square" :class="myFile.deleted ? 'opacity-50' : ''" />
                        <div v-else class="flex flex-col justify-center items-center px-3"
                            :class="myFile.deleted ? 'opacity-50' : ''">
                            <PaperClipIcon class="w-10 h-10 mb-3" />

                            <small class="text-center">
                                {{ (myFile.file || myFile).name }}
                            </small>
                        </div>
                    </div>
                    <small class="text-red-500">{{ attachmentErrors[ind] }}</small>
                </div>
            </div>

        </div>

        <div class="flex gap-2 py-3 px-4">
            <button type="button"
                class="flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 w-full relative">
                <PaperClipIcon class="w-4 h-4 mr-2" />
                Attach Files
                <input @click.stop @change="onAttachmentChoose" type="file" multiple accept="image/*,video/*"
                    class="absolute left-0 top-0 right-0 bottom-0 opacity-0">
            </button>
            <button type="button"
                class="flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 w-full"
                @click="submit">
                <BookmarkIcon class="w-4 h-4 mr-2" />
                Submit
            </button>
        </div>
    </BaseModal>
</template>


<style scoped>

.multiselect__option .flex {
    position: relative;
    z-index: 1;
    align-items: center;
    justify-content: space-between;
}


.multiselect__option input[type="number"] {
    z-index: 2;
    position: relative;
    background-color: white;
    border: 1px solid #d1d5db;
    
    border-radius: 0.375rem;
    
    padding: 0.25rem 0.5rem;
    width: 3.5rem;
    color: #111827;
    
    -moz-appearance: textfield;
    
}


.multiselect__option--highlight {
    background-color: #e0f2f1 !important;
    
}


.multiselect__option input[type="number"]::-webkit-inner-spin-button,
.multiselect__option input[type="number"]::-webkit-outer-spin-button {
    opacity: 1;
}
</style>

<script setup>
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'
import { useForm } from '@inertiajs/vue3'
import { availableRoles } from '@/data/roles'
const props = defineProps({
  roles: Array,
  isMyProfile: Boolean,
})
const form = useForm({
  roles: [...props.roles],
})
function save() {
  form.post(route('profile.roles.update'), {
    preserveScroll: true,
  })
}
</script>
<template>
  <div class="p-4">
    <div v-if="!isMyProfile">
      <h3 class="text-gray-400 mb-2">Artistic Roles</h3>
      <ul class="list-disc list-inside space-y-1 text-gray-200">
        <li v-for="role in roles" :key="role">{{ role }}</li>
      </ul>
      <p v-if="!roles.length" class="text-gray-500">No roles specified.</p>
    </div>
    <div v-else>
      <label class="block text-sm font-medium text-gray-300 mb-1">
        Your Artistic Roles
      </label>
      <Multiselect v-model="form.roles" :options="availableRoles.map(r => r.name)" :multiple="true" :taggable="true"
        placeholder="Select roles" class="mb-2 w-full" />
      <button @click="save" :disabled="form.processing"
        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-500 disabled:opacity-50">
        Save
      </button>
    </div>
  </div>
</template>

<script setup>
import BaseModal from "@/Components/app/BaseModal.vue";
import Multiselect from 'vue-multiselect';
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: Boolean,
    post: Object
});
const emit = defineEmits(['update:modelValue']);

const show = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

const availableRoles = computed(() => (props.post.roles || []).map(r => r.name));
const selectedRole = ref('');

const form = useForm({
    role: '',
});

function closeModal() {
    show.value = false;
    selectedRole.value = '';
    form.reset();
}

function submit() {
    if (!selectedRole.value) return;

    form.role = selectedRole.value;

    form.post(route('post.join', props.post.id), {
        preserveScroll: true,
        onSuccess: () => closeModal()
    });
}
</script>

<template>
    <BaseModal title="Join Event" v-model="show" @hide="closeModal">
        <div class="p-4">
            <p class="mb-3 dark:text-white">Choose the role you'd like to join as for this event:</p>

            <Multiselect
                v-model="selectedRole"
                :options="availableRoles"
                placeholder="Select a role"
                class="mb-4"
            />

            <button
                @click="submit"
                class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded w-full"
            >
                Request to Join
            </button>
        </div>
    </BaseModal>
</template>

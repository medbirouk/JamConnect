<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'
import { availableRoles } from '@/data/roles'
const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  roles: [],
  demo: null,
});
const handleFileUpload = (e) => {
  form.demo = e.target.files[0];
};
const submit = () => {
  const originalRoles = [...form.roles];
  form.roles = form.roles.map(r => r.name);
  form.post(route('register'), {
    onError: (errors) => {
      console.log(errors)
      form.roles = originalRoles;
    },
    forceFormData: true,
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>
<template>
  <GuestLayout>

    <Head title="Register" />
    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <InputLabel for="name" value="Name" />
        <TextInput id="name" v-model="form.name" required autofocus class="mt-1 block w-full" autocomplete="name" />
        <InputError :message="form.errors.name" class="mt-2" />
      </div>
      <div>
        <InputLabel for="email" value="Email" />
        <TextInput id="email" v-model="form.email" required class="mt-1 block w-full" autocomplete="username" />
        <InputError :message="form.errors.email" class="mt-2" />
      </div>
      <div>
        <InputLabel for="password" value="Password" />
        <TextInput id="password" type="password" v-model="form.password" required class="mt-1 block w-full"
          autocomplete="new-password" />
        <InputError :message="form.errors.password" class="mt-2" />
      </div>
      <div>
        <InputLabel for="password_confirmation" value="Confirm Password" />
        <TextInput id="password_confirmation" type="password" v-model="form.password_confirmation" required
          class="mt-1 block w-full" autocomplete="new-password" />
        <InputError :message="form.errors.password_confirmation" class="mt-2" />
      </div>
      <Multiselect v-model="form.roles" :options="availableRoles" :multiple="true" :close-on-select="false"
        :show-labels="false" placeholder="Select your roles" track-by="name" label="name" />
      <div>
        <InputLabel for="demo" value="Upload a demo (mp4, mp3, wav, …)" />
        <input id="demo" type="file" @change="handleFileUpload" accept="audio/*,video/*" class="mt-1 block w-full text-sm text-gray-700
                        bg-white border border-gray-300 rounded-lg shadow-sm
                        file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                        file:text-sm file:font-semibold file:bg-indigo-50
                        file:text-indigo-700 hover:file:bg-indigo-100" />
        <InputError :message="form.errors.demo" class="mt-2" />
      </div>
      <div class="flex items-center justify-end space-x-4 pt-2">
        <Link :href="route('login')" class="underline text-sm text-gray-600 dark:text-gray-400
                       hover:text-gray-900 dark:hover:text-gray-100">
        Already registered?
        </Link>
        <PrimaryButton :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
          Register
        </PrimaryButton>
      </div>
    </form>
  </GuestLayout>
</template>
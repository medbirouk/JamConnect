<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <AdminLayout>
    <Head title="Admin Dashboard" />

    <div class="max-w-4xl mx-auto p-8">
      <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-4">
        Admin Dashboard
      </h1>

      <p class="text-lg text-gray-600 dark:text-gray-300 mb-8">
        Welcome to the Admin Dashboard!
      </p>

      <!-- actions row -->
      <div class="flex flex-wrap gap-4">
        <Link
          :href="route('admin.users.pending')"
          class="inline-flex items-center justify-center
                 rounded-md bg-indigo-600 px-6 py-3 text-sm font-semibold text-white
                 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400
                 transition"
        >
          View Pending Approvals
        </Link>

        <Link
          :href="route('admin.users.list')"
          class="inline-flex items-center justify-center
                 rounded-md bg-gray-600 px-6 py-3 text-sm font-semibold text-white
                 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400
                 transition"
        >
          View All Users
        </Link>
      </div>
    </div>
  </AdminLayout>
</template>

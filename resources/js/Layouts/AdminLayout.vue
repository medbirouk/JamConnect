<script setup>
import { ref } from 'vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import TextInput from "@/Components/TextInput.vue"
import { MoonIcon } from '@heroicons/vue/24/solid'
const showingNavigationDropdown = ref(false)
const keywords = ref(usePage().props.search || '')
const authAdmin = usePage().props.auth?.admin
function search() {
  router.get(route('search', encodeURIComponent(keywords.value)))
}
function toggleDarkMode() {
  const html = window.document.documentElement
  if (html.classList.contains('dark')) {
    html.classList.remove('dark')
    localStorage.setItem('darkMode', '0')
  } else {
    html.classList.add('dark')
    localStorage.setItem('darkMode', '1')
  }
}
</script>
<template>
  <div class="h-full overflow-hidden flex flex-col bg-gray-100 dark:bg-gray-800">
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-2 h-16">
          <div class="flex mr-2">
            <div class="shrink-0 flex items-center">
              <Link :href="route('admin.dashboard')">
              <ApplicationLogo class="block fill-current text-gray-800 dark:text-gray-200 h-40" />
              </Link>
            </div>
          </div>
          <div class="flex items-center justify-end flex-1">
            <button @click="toggleDarkMode" class="dark:text-white">
              <MoonIcon class="w-5 h-5" />
            </button>
          </div>
          <div class="hidden sm:flex sm:items-center">
            <div class="ms-3 relative">
              <Dropdown v-if="authAdmin" align="right" width="48">
                <template #trigger>
                  <span class="inline-flex rounded-md">
                    <button type="button"
                      class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                      {{ authAdmin.name }}
                      <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                          clip-rule="evenodd" />
                      </svg>
                    </button>
                  </span>
                </template>
                <template #content>
                  <DropdownLink :href="route('admin.logout')" method="post" as="button">
                    Log Out
                  </DropdownLink>
                </template>
              </Dropdown>
              <div v-else>
                <Link :href="route('admin.login')" class="dark:text-gray-100">Login</Link>
              </div>
            </div>
          </div>
          <div class="-me-2 flex items-center sm:hidden">
            <button @click="showingNavigationDropdown = !showingNavigationDropdown"
              class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
              <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                  stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                  stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>
      <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
          <template v-if="authAdmin">
            <div class="px-4">
              <div class="font-medium text-base text-gray-800 dark:text-gray-200">
                {{ authAdmin.name }}
              </div>
              <div class="font-medium text-sm text-gray-500">{{ authAdmin.email }}</div>
            </div>
            <div class="mt-3 space-y-1">
              <ResponsiveNavLink :href="route('admin.logout')" method="post" as="button">
                Log Out
              </ResponsiveNavLink>
            </div>
          </template>
          <template v-else>
            <ResponsiveNavLink :href="route('admin.login')">
              Login
            </ResponsiveNavLink>
          </template>
        </div>
      </div>
    </nav>
    <header class="bg-white dark:bg-gray-800 shadow" v-if="$slots.header">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>
    <main class="flex-1 overflow-auto px-4 py-6">
      <slot />
    </main>
  </div>
</template>

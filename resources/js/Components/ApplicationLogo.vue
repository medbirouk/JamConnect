<script setup>
import { ref, onMounted } from 'vue'

const isDark = ref(false)

onMounted(() => {
    
    isDark.value = document.documentElement.classList.contains('dark')

    
    const observer = new MutationObserver(() => {
        isDark.value = document.documentElement.classList.contains('dark')
    })
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] })
})
</script>

<template>
    <img v-if="!isDark" src="/img/JamLogo.png" alt="JamConnect Light Logo" class="['mx-auto w-auto', $attrs.class ?? 'h-40']" />
    <img v-else src="/img/JamLogoDark.png" alt="JamConnect Dark Logo" class="['mx-auto w-auto', $attrs.class ?? 'h-40']" />
</template>
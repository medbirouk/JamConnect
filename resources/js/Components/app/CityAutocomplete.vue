<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const emit = defineEmits(['update:city'])
const input = ref('')
const suggestions = ref([])

function searchCities() {
  if (input.value.length < 2) {
    suggestions.value = []
    return
  }

  console.log('[searchCities] Searching for:', input.value)

  axios.get('/location/autocomplete', {
    params: {
      q: input.value
    }
  })
    .then(res => {
      console.log('[searchCities] API results:', res.data)

      if (!Array.isArray(res.data)) {
        console.warn('[searchCities] API returned non-array data:', res.data)
        suggestions.value = []
        return
      }

      suggestions.value = res.data.map(item => {
        const parts = item.display_name.split(',')
        const city = parts[0]?.trim() || ''
        const country = item.address?.country || ''

        return {
          name: `${city}, ${country}`,
          city,
          country,
          lat: item.lat,
          lon: item.lon
        }
      })

    })
    .catch((err) => {
      console.error('[searchCities] API error:', err)
      suggestions.value = []
    })
}

function selectCity(city) {
  console.log('[selectCity] Selected:', city)
  input.value = city.name
  suggestions.value = []
  emit('update:city', city.name)
}

onMounted(() => {
  console.log('[onMounted] Attempting to use geolocation...')

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      async position => {
        const { latitude, longitude } = position.coords
        console.log('[onMounted] Got coordinates:', latitude, longitude)

        try {
          const res = await axios.get('/api/reverse-geocode', {
            params: {
              lat: latitude,
              lon: longitude,
            }
          })

          console.log('[onMounted] Reverse geocode response:', res.data)

          const address = res.data.address || {}
          const city = address.city || address.town || address.village || ''
          const country = address.country || ''

          const fallbackCity = city || res.data.display_name?.split(',')[0]?.trim()
          const display = `${fallbackCity}, ${country}`

          console.log('[onMounted] Parsed location:', display)

          input.value = display
          emit('update:city', display)
        } catch (err) {
          console.error('[onMounted] Error from reverse geocode:', err)
        }
      },
      error => {
        console.error('[onMounted] Geolocation permission denied or failed:', error)
      }
    )
  } else {
    console.warn('[onMounted] Geolocation is not supported by this browser.')
  }
})
</script>



<template>
    <div class="relative w-full">
        <input v-model="input" @input="searchCities" type="text" placeholder="Enter city"
        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm" />
        <ul v-if="suggestions.length" class="absolute w-full mt-1 z-10 rounded shadow border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
            <li v-for="(city, i) in suggestions" :key="i" @click="selectCity(city)"
                class="p-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-white transition">
                {{ city.name }}
            </li>
        </ul>
    </div>
</template>

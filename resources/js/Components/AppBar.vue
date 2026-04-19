<template>
  <v-app-bar flat color="primary">
    <v-app-bar-title>
      <div class="d-flex flex-column">
        <slot name="title">{{ title }}</slot>
        <div v-if="$slots.description || description">
          <slot name="description" class="text-body-1">qwe s{{ description }}</slot>
        </div>
      </div>
    </v-app-bar-title>

    <template v-slot:append>
      <v-btn icon @click="toggleTheme">
        <v-icon>{{ themeIcon }}</v-icon>
      </v-btn>
    </template>
  </v-app-bar>
</template>

<script setup>
import { useTheme } from 'vuetify'
import { computed } from 'vue'

const theme = useTheme()
const themes = ['brand', 'dark']

const themeIcon = computed(() => {
  if (theme.global.name.value === 'dark') return 'mdi-weather-night'
  if (theme.global.name.value === 'brand') return 'mdi-leaf'
})

function toggleTheme () {
  const currentIndex = themes.indexOf(theme.global.name.value)
  const nextIndex = (currentIndex + 1) % themes.length
  theme.global.name.value = themes[nextIndex]
}
defineProps({
  title: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  }
})
</script>
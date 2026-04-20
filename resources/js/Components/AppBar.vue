<template>
  <v-toolbar flat border="b" height="64" color="surface">
    <!-- Custom title+description area (manual layout avoids v-toolbar-title constraints) -->
    <div class="appbar-title-area">
      <div class="text-subtitle-1 font-weight-bold">
        <slot name="title">{{ title }}</slot>
      </div>
      <div v-if="$slots.description || description" class="text-caption text-medium-emphasis mt-n1">
        <slot name="description">{{ description }}</slot>
      </div>
    </div>

    <v-spacer />

    <!-- Actions -->
    <v-btn icon @click="toggleTheme" class="mr-1">
      <v-icon>{{ themeIcon }}</v-icon>
      <v-tooltip activator="parent" location="bottom">Toggle theme</v-tooltip>
    </v-btn>

    <v-btn
      v-if="hasTransactionsSidebar"
      icon
      @click="toggleTransactionsDrawer"
      :variant="transactionsDrawer ? 'tonal' : 'text'"
      :color="transactionsDrawer ? 'primary' : undefined"
      class="mr-2"
    >
      <v-icon>{{ transactionsDrawer ? 'mdi-cart' : 'mdi-cart-outline' }}</v-icon>
      <v-tooltip activator="parent" location="bottom">
        {{ transactionsDrawer ? 'Close cart panel' : 'Open cart panel' }}
      </v-tooltip>
    </v-btn>
  </v-toolbar>
</template>

<script setup>
import { useTheme } from 'vuetify'
import { computed, inject } from 'vue'

const theme = useTheme()
const themes = ['brand', 'dark']

const themeIcon = computed(() => {
  if (theme.global.name.value === 'dark') return 'mdi-weather-night'
  if (theme.global.name.value === 'brand') return 'mdi-leaf'
  return 'mdi-theme-light-dark'
})

function toggleTheme() {
  const currentIndex = themes.indexOf(theme.global.name.value)
  const nextIndex = (currentIndex + 1) % themes.length
  theme.global.name.value = themes[nextIndex]
}

// Injected from AuthenticatedLayout — controls the right panel
const transactionsDrawer = inject('transactionsDrawer', null)
const toggleTransactionsDrawer = inject('toggleTransactionsDrawer', () => {})
const hasTransactionsSidebar = inject('hasTransactionsSidebar', false)

defineProps({
  title: { type: String, default: '' },
  description: { type: String, default: '' }
})
</script>

<style scoped>
.appbar-title-area {
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding-left: 16px;
  line-height: 1.3;
  flex: 1;
  min-width: 0;
  overflow: hidden;
}

/* Strip margin/padding from any block elements passed in via the description slot */
.appbar-title-area :deep(p) {
  margin: 0;
  padding: 0;
}
</style>
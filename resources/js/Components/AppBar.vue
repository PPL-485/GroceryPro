<template>
  <v-toolbar flat border="b" height="64" color="surface">
    <v-btn
      v-if="!mdAndUp"
      icon
      variant="text"
      size="small"
      class="appbar-menu-btn ml-2 mr-1"
      @click="toggleNavigationDrawer"
      aria-label="Open navigation menu"
    >
      <v-icon>mdi-menu</v-icon>
      <v-tooltip activator="parent" location="bottom">Open navigation</v-tooltip>
    </v-btn>

    <!-- Custom title+description area (manual layout avoids v-toolbar-title constraints) -->
    <div class="appbar-title-area">
      <div class="appbar-title text-subtitle-1 font-weight-bold">
        <slot name="title">{{ title }}</slot>
      </div>
      <div v-if="mdAndUp && ($slots.description || description)" class="text-caption text-medium-emphasis mt-n1">
        <slot name="description">{{ description }}</slot>
      </div>
    </div>

    <v-spacer />

    <!-- Notifications -->
    <v-menu location="bottom end">
      <template v-slot:activator="{ props }">
        <v-btn icon v-bind="props" class="mr-1">
          <v-badge
            v-if="unreadNotifications.length > 0"
            :content="unreadNotifications.length"
            color="error"
          >
            <v-icon>mdi-bell-outline</v-icon>
          </v-badge>
          <v-icon v-else>mdi-bell-outline</v-icon>
          <v-tooltip activator="parent" location="bottom">Notifications</v-tooltip>
        </v-btn>
      </template>

      <v-list min-width="300" max-width="400">
        <v-list-item v-if="unreadNotifications.length === 0">
          <v-list-item-title class="text-caption text-grey pa-2 text-center">No new notifications</v-list-item-title>
        </v-list-item>
        <v-list-item
          v-for="notification in unreadNotifications"
          :key="notification.id"
          class="border-b"
          lines="two"
        >
          <template v-slot:prepend>
            <v-avatar color="red-lighten-5" size="36">
              <v-icon color="error" size="small">mdi-alert</v-icon>
            </v-avatar>
          </template>
          <v-list-item-title class="font-weight-bold text-body-2">Low Stock Alert</v-list-item-title>
          <v-list-item-subtitle class="text-caption mt-1" style="white-space: normal;">
            {{ notification.data.product_name }} is low on stock ({{ notification.data.stock_qty }} left).
          </v-list-item-subtitle>
          <template v-slot:append>
            <v-btn icon="mdi-close" variant="text" size="small" color="grey" @click.stop="deleteNotification(notification.id)"></v-btn>
          </template>
        </v-list-item>
      </v-list>
    </v-menu>

    <!-- Actions -->

    <v-btn
      id="btn-toggle-cart"
      v-if="hasTransactionsSidebar"
      icon
      @click="toggleTransactionsDrawer"
      :variant="transactionsDrawer ? 'tonal' : 'text'"
      :color="transactionsDrawer ? 'primary' : undefined"
      class="mr-2"
    >
      <v-badge
        v-if="cartItemCount > 0"
        :content="cartItemCount"
        color="error"
      >
        <v-icon>{{ transactionsDrawer ? 'mdi-cart' : 'mdi-cart-outline' }}</v-icon>
      </v-badge>
      <v-icon v-else>{{ transactionsDrawer ? 'mdi-cart' : 'mdi-cart-outline' }}</v-icon>
      <v-tooltip activator="parent" location="bottom">
        {{ transactionsDrawer ? 'Close cart panel' : 'Open cart panel' }}<span v-if="cartItemCount > 0"> · {{ cartItemCount }} product{{ cartItemCount !== 1 ? 's' : '' }} · {{ formatCurrency(cartSubtotal) }}</span>
      </v-tooltip>
    </v-btn>
  </v-toolbar>
</template>

<script setup>
import { useTheme, useDisplay } from 'vuetify'
import { computed, inject, onMounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

const page = usePage()
const unreadNotifications = computed(() => page.props.auth.unreadNotifications || [])

function deleteNotification(id) {
  router.delete(route('notifications.destroy', id), {
    preserveScroll: true,
  })
}

const theme = useTheme()
const { mdAndUp } = useDisplay()
const themes = ['brand', 'dark']
const THEME_KEY = 'grocerypro-theme'

// Restore saved theme on every page load
onMounted(() => {
  const saved = localStorage.getItem(THEME_KEY)
  if (saved && themes.includes(saved)) {
    theme.global.name.value = saved
  }
})


// Injected from AuthenticatedLayout — controls the right panel
const transactionsDrawer = inject('transactionsDrawer', null)
const toggleTransactionsDrawer = inject('toggleTransactionsDrawer', () => {})
const hasTransactionsSidebar = inject('hasTransactionsSidebar', false)
const toggleNavigationDrawer = inject('toggleNavigationDrawer', () => {})

defineProps({
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  cartItemCount: { type: Number, default: 0 },
  cartSubtotal: { type: Number, default: 0 }
})

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(value || 0)
}
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

.appbar-menu-btn {
  flex: 0 0 auto;
}

.appbar-title {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Strip margin/padding from any block elements passed in via the description slot */
.appbar-title-area :deep(p) {
  margin: 0;
  padding: 0;
}

@media (max-width: 959px) {
  .appbar-title-area {
    padding-left: 8px;
  }
}
</style>

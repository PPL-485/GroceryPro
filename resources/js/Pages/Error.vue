<script setup>
import { computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'

const props = defineProps({
  status: Number,
})

const page = usePage()

const title = computed(() => {
  return {
    503: '503: Service Unavailable',
    500: '500: Server Error',
    404: '404: Page Not Found',
    403: '403: Access Denied',
  }[props.status] || 'Error'
})

const description = computed(() => {
  return {
    503: 'Sorry, we are doing some maintenance. Please check back soon.',
    500: 'Whoops, something went wrong on our servers.',
    404: 'Sorry, the page you are looking for could not be found.',
    403: 'Sorry, you do not have the required permissions to access this feature.',
  }[props.status] || 'An unexpected error occurred.'
})

const layout = computed(() => {
    return page.props.auth?.user ? AuthenticatedLayout : GuestLayout;
});
</script>

<template>
  <Head :title="title" />
  
  <component :is="layout">
      <template #header-title v-if="page.props.auth?.user">
          Restricted Area
      </template>
      <template #header-description v-if="page.props.auth?.user">
          Unauthorized Access Attempt
      </template>

      <div class="d-flex flex-column align-center justify-center text-center py-16 mt-8">
          <v-icon
              :color="status === 403 ? 'error' : 'warning'"
              size="150"
              class="mb-6 opacity-80"
          >
              {{ status === 403 ? 'mdi-shield-lock-outline' : (status === 404 ? 'mdi-magnify-close' : 'mdi-alert-circle-outline') }}
          </v-icon>
          <div class="text-h3 font-weight-bold mb-4 text-grey-darken-3">{{ title }}</div>
          <div class="text-h6 text-grey-darken-1 mb-8 px-4" style="max-width: 600px; line-height: 1.6;">
              {{ description }}
          </div>
          
          <v-btn
              color="primary"
              size="large"
              variant="flat"
              prepend-icon="mdi-home"
              href="/dashboard"
              elevation="2"
              class="rounded-lg px-8 text-none"
              height="54"
          >
              Back to Dashboard
          </v-btn>
      </div>
  </component>
</template>

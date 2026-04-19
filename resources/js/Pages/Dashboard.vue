<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header-title>
            Dashboard
        </template>
        
        <template #header-description>
            <p class="text-sm text-gray-500">
                Welcome to your dashboard overview
            </p>
        </template>
        
        <div class="overflow-hidden shadow-sm sm:rounded-lg">
            <p class="text-body-1">Ini Dashboard</p>
            
            <v-text-field
  label="Label"
  prepend-icon="$vuetify"
  variant="solo-filled"
></v-text-field>
        </div>
    </AuthenticatedLayout>
</template>

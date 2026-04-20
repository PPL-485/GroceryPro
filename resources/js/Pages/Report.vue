<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Report" />

    <AuthenticatedLayout>
        <template #header-title>
            Report
        </template>
        
        <template #header-description>
            <p class="text-sm text-gray-500">
                Welcome to your Report
            </p>
        </template>
        
        <div class="overflow-hidden shadow-sm sm:rounded-lg">
            <p class="text-body-1">Ini Report</p>
            
            <v-text-field
  label="Label"
  prepend-icon="$vuetify"
  variant="solo-filled"
></v-text-field>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>

        <template #header-description>
        <p class="text-sm text-gray-500">
            Overview of your grocery store performance
        </p>
        </template>
        
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="text-gray-900">You're logged in!</div>
        </div>
    </AuthenticatedLayout>
</template>

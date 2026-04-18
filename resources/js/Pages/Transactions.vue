<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Transactions" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Transactions</h2>
        </template> 

        <template #header-description>
        <p class="text-sm text-gray-500">
            Search products to add to cart
        </p>
        </template>
        
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="text-gray-900">Transactions!</div>
        </div>
    </AuthenticatedLayout>
</template>

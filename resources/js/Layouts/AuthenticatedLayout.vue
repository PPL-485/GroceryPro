<script setup>
import Sidebar from '@/Components/Sidebar.vue';
</script>

<template>
    <v-app>
        <Sidebar />
        <v-main>
            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow">
                <div class="mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                    <slot name="header-description" />
                </div>
            </header>

            <!-- Page Content -->
            <div class="mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot />
            </div>
        </v-main>
    </v-app>
</template>

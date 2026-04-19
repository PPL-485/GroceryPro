<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import AppBar from '@/Components/AppBar.vue';

</script>

<template>
    <v-app>
        <Sidebar />
            <AppBar>
                <template #title>
                    <slot name="header-title">{{ headerTitle }}</slot>
                </template>
                <template #description>
                    <slot name="header-description">{{ headerDescription }}</slot>
                </template>
            </AppBar>
        <v-main>
            <!-- Page Content -->
            <div class="mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot />
            </div>
        </v-main>
    </v-app>
</template>

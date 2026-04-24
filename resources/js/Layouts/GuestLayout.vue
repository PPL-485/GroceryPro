<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <v-app style="background-color: #FAF9F6; font-family: 'Inter', sans-serif;">
        <v-main>
            <v-container class="fill-height d-flex align-center justify-center position-relative overflow-hidden pa-4" fluid>
                <!-- Blurred Background Blobs -->
                <div style="position: absolute; filter: blur(80px); border-radius: 50%; opacity: 0.4; z-index: 1; width: 400px; height: 400px; background: #E8F5E9; top: -100px; left: -100px;"></div>
                <div style="position: absolute; filter: blur(80px); border-radius: 50%; opacity: 0.4; z-index: 1; width: 350px; height: 350px; background: #FFF3E0; bottom: -50px; right: -50px;"></div>
                <div style="position: absolute; filter: blur(80px); border-radius: 50%; opacity: 0.4; z-index: 1; width: 300px; height: 300px; background: #F1F8E9; top: 20%; right: 10%;"></div>
                
                <div style="position: relative; z-index: 10; width: 100%; max-width: 380px;">
                    <slot />
                </div>
            </v-container>
        </v-main>
    </v-app>
</template>

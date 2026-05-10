<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <v-app style="background: linear-gradient(135deg, #FCFBF8 0%, #F5F2ED 100%); font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh;">
        <v-main>
            <v-container class="fill-height d-flex align-center justify-center position-relative overflow-hidden pa-4 pa-sm-6" fluid>
                <!-- Animated Background Blobs -->
                <div style="position: absolute; filter: blur(100px); border-radius: 50%; opacity: 0.3; z-index: 1; width: 500px; height: 500px; background: linear-gradient(135deg, #E8F5E9, #C8E6C9); top: -150px; left: -150px; animation: float 6s ease-in-out infinite;"></div>
                <div style="position: absolute; filter: blur(100px); border-radius: 50%; opacity: 0.25; z-index: 1; width: 400px; height: 400px; background: linear-gradient(135deg, #FFE0B2, #FFCC80); bottom: -100px; right: -100px; animation: float 8s ease-in-out infinite reverse;"></div>
                <div style="position: absolute; filter: blur(100px); border-radius: 50%; opacity: 0.2; z-index: 1; width: 350px; height: 350px; background: linear-gradient(135deg, #F1F8E9, #DCEDC8); top: 15%; right: 5%; animation: float 7s ease-in-out infinite;"></div>
                
                <div style="position: relative; z-index: 10; width: 100%; max-width: 420px;">
                    <!-- Header with Logo -->
                    <div style="text-align: center; margin-bottom: 32px;">
                        <div style="display: inline-flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                            <v-icon icon="mdi-cart-outline" color="#2E6B3B" size="32"></v-icon>
                            <span style="font-size: 20px; font-weight: 700; color: #1B1B1B; letter-spacing: -0.5px;">GroceryPro</span>
                        </div>
                    </div>
                    
                    <!-- Slot for Page Content -->
                    <slot />
                    
                    <!-- Footer -->
                    <div style="text-align: center; margin-top: 32px;">
                        <p style="color: #999; font-size: 12px; margin: 0;">
                            © 2026 GroceryPro. All rights reserved.
                        </p>
                    </div>
                </div>
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>
@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(20px);
    }
}
</style>

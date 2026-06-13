<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <!-- Back to Login (fixed bottom-left) -->
        <div class="back-home-wrapper">
            <Link :href="route('login')" class="back-home-link">
                <v-icon icon="mdi-arrow-left" size="small"></v-icon>
                Back to Login
            </Link>
        </div>

        <v-card class="pa-8 pa-sm-8" elevation="2" rounded="xl" color="white">
            <div class="text-center mb-8">
                <v-sheet color="#386641" rounded="lg" width="48" height="48" class="d-flex align-center justify-center mx-auto mb-4">
                    <v-icon icon="mdi-lock-reset" color="white" size="24"></v-icon>
                </v-sheet>
                <h1 class="text-h4 font-weight-medium mb-1">Forgot Password?</h1>
                <p class="text-body-2 mb-0" style="color: #666; letter-spacing: 0.2px;">
                    No worries! Enter your email and we'll send you a reset link.
                </p>
            </div>

            <!-- Success Status -->
            <v-alert
                v-if="status"
                type="success"
                variant="tonal"
                class="mb-6"
                density="comfortable"
                icon="mdi-check-circle-outline"
            >
                {{ status }}
            </v-alert>

            <form @submit.prevent="submit">
                <div class="mb-6">
                    <label class="text-caption font-weight-bold d-block mb-1" style="color: #444;">Email Address</label>
                    <v-text-field
                        id="input-forgot-email"
                        v-model="form.email"
                        type="email"
                        placeholder="Enter your email"
                        prepend-inner-icon="mdi-email-outline"
                        variant="outlined"
                        hide-details="auto"
                        :error-messages="form.errors.email"
                        required
                        autofocus
                        autocomplete="username"
                        bg-color="transparent"
                        rounded="lg"
                    ></v-text-field>
                </div>

                <v-btn
                    id="btn-send-reset"
                    type="submit"
                    block
                    height="48"
                    color="#B26B43"
                    class="text-none font-weight-bold text-body-1"
                    rounded="lg"
                    :loading="form.processing"
                    :disabled="form.processing"
                    style="letter-spacing: 0.5px; box-shadow: 0 4px 12px rgba(178, 107, 67, 0.2); color: white;"
                >
                    Send Reset Link
                    <v-icon icon="mdi-send" size="small" style="margin-left: 8px;"></v-icon>
                </v-btn>
            </form>
        </v-card>
    </GuestLayout>
</template>

<style scoped>
.back-home-wrapper {
    position: fixed;
    bottom: 1.75rem;
    left: 1.75rem;
    z-index: 100;
}
.back-home-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #2E6B3B;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 999px;
    background: #E8F5E9;
    border: 1px solid #C8E6C9;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: background 0.2s ease, box-shadow 0.2s ease, color 0.2s ease;
}
.back-home-link:hover {
    background: #C8E6C9;
    color: #1B5E20;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
}
</style>

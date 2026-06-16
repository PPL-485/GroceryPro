<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    password: '',
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <div class="back-home-wrapper">
            <Link :href="route('login')" class="back-home-link">
                <v-icon icon="mdi-arrow-left" size="small"></v-icon>
                Back to Login
            </Link>
        </div>

        <v-card class="auth-card pa-8 pa-sm-8" elevation="0" rounded="xl">
            <div class="text-center mb-8">
                <v-sheet color="primary" rounded="lg" width="48" height="48" class="auth-logo d-flex align-center justify-center mx-auto mb-4">
                    <v-icon icon="mdi-shield-lock-outline" color="white" size="24"></v-icon>
                </v-sheet>
                <h1 class="text-h4 font-weight-bold mb-1">Confirm Password</h1>
                <p class="text-body-2 text-medium-emphasis mb-0">Please confirm your password before continuing</p>
            </div>

            <form @submit.prevent="submit">
                <div class="mb-6">
                    <label class="auth-label text-caption font-weight-bold d-block mb-1">Password</label>
                    <v-text-field
                        id="input-confirm-password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Enter your account password"
                        prepend-inner-icon="mdi-lock-outline"
                        :append-inner-icon="showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                        variant="outlined"
                        hide-details="auto"
                        :error-messages="form.errors.password"
                        required
                        autocomplete="current-password"
                        autofocus
                        @click:append-inner="showPassword = !showPassword"
                    ></v-text-field>
                </div>

                <v-btn
                    id="btn-confirm-password"
                    type="submit"
                    block
                    height="48"
                    color="primary"
                    class="text-none font-weight-bold text-body-1"
                    rounded="lg"
                    :loading="form.processing"
                    :disabled="form.processing"
                >
                    Confirm
                </v-btn>
            </form>
        </v-card>
    </GuestLayout>
</template>

<style scoped>
.auth-card {
    border: 1px solid rgba(var(--v-border-color), 0.18);
    background: rgb(var(--v-theme-surface)) !important;
    box-shadow: none !important;
}

.auth-logo {
    border: 1px solid rgba(var(--v-theme-primary), 0.18);
}

.auth-label {
    color: rgba(var(--v-theme-on-surface), 0.72);
}

.back-home-wrapper {
    position: fixed;
    bottom: 1.5rem;
    left: 1.5rem;
    z-index: 100;
}

.back-home-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: rgb(var(--v-theme-primary));
    font-size: 0.875rem;
    font-weight: 700;
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 999px;
    background: rgb(var(--v-theme-surface));
    border: 1px solid rgba(var(--v-theme-primary), 0.2);
    box-shadow: none;
    transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}

.back-home-link:hover {
    background: rgba(var(--v-theme-primary), 0.06);
    border-color: rgba(var(--v-theme-primary), 0.32);
}

.auth-card :deep(.v-field) {
    border-radius: 14px;
}

.auth-card :deep(.v-btn) {
    letter-spacing: 0;
}

@media (max-width: 600px) {
    .back-home-wrapper {
        position: static;
        margin-bottom: 16px;
        text-align: center;
    }
}
</style>

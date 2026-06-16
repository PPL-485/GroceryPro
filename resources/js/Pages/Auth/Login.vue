<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const snackbar = ref(false);
const showPassword = ref(false);

onMounted(() => {
    if (props.status) {
        snackbar.value = true;
    }
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="back-home-wrapper">
            <Link href="/" class="back-home-link">
                <v-icon icon="mdi-arrow-left" size="small"></v-icon>
                Back to Home
            </Link>
        </div>

        <v-card class="auth-card pa-8 pa-sm-8" elevation="0" rounded="xl">
            <div class="text-center mb-8">
                <v-sheet color="primary" rounded="lg" width="48" height="48" class="auth-logo d-flex align-center justify-center mx-auto mb-4">
                    <v-icon icon="mdi-cart-outline" color="white" size="24"></v-icon>
                </v-sheet>
                <h1 class="text-h4 font-weight-bold mb-1">Welcome Back</h1>
                <p class="text-body-2 text-medium-emphasis mb-0">Sign in to manage checkout, stock, and reports</p>
            </div>

            <v-snackbar
                v-model="snackbar"
                color="primary"
                rounded="lg"
                timeout="5000"
                location="top"
                elevation="0"
            >
                <div class="d-flex align-center ga-3">
                    <v-icon icon="mdi-check-circle" color="white"></v-icon>
                    <span class="font-weight-semibold">{{ status }}</span>
                </div>
                <template #actions>
                    <v-btn icon="mdi-close" size="small" color="white" variant="text" @click="snackbar = false"></v-btn>
                </template>
            </v-snackbar>

            <form @submit.prevent="submit">
                <div class="mb-5">
                    <label class="auth-label text-caption font-weight-bold d-block mb-1">Email Address</label>
                    <v-text-field
                        id="input-email"
                        v-model="form.email"
                        placeholder="Enter your account email"
                        prepend-inner-icon="mdi-email-outline"
                        variant="outlined"
                        hide-details="auto"
                        :error-messages="form.errors.email"
                        required
                        autofocus
                    ></v-text-field>
                </div>

                <div class="mb-2">
                    <label class="auth-label text-caption font-weight-bold d-block mb-1">Password</label>
                    <v-text-field
                        id="input-password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Enter account password"
                        prepend-inner-icon="mdi-lock-outline"
                        :append-inner-icon="showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                        variant="outlined"
                        hide-details="auto"
                        :error-messages="form.errors.password"
                        required
                        @click:append-inner="showPassword = !showPassword"
                    ></v-text-field>
                </div>

                <div class="d-flex justify-end mb-6">
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="auth-muted-link text-caption font-weight-bold"
                    >
                        Forgot password?
                    </Link>
                </div>

                <v-btn
                    id="btn-login"
                    type="submit"
                    block
                    height="48"
                    color="primary"
                    class="text-none font-weight-bold text-body-1"
                    rounded="lg"
                    :loading="form.processing"
                    :disabled="form.processing"
                >
                    Sign In
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

.auth-muted-link {
    color: rgb(var(--v-theme-primary));
    text-decoration: none;
}

.auth-muted-link:hover {
    text-decoration: underline;
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

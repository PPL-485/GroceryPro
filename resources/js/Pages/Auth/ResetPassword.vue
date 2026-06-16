<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirm = ref(false);

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <div class="back-home-wrapper">
            <Link :href="route('login')" class="back-home-link">
                <v-icon icon="mdi-arrow-left" size="small"></v-icon>
                Back to Login
            </Link>
        </div>

        <v-card class="auth-card pa-8 pa-sm-8" elevation="0" rounded="xl">
            <div class="text-center mb-8">
                <v-sheet color="primary" rounded="lg" width="48" height="48" class="auth-logo d-flex align-center justify-center mx-auto mb-4">
                    <v-icon icon="mdi-shield-key-outline" color="white" size="24"></v-icon>
                </v-sheet>
                <h1 class="text-h4 font-weight-bold mb-1">Reset Password</h1>
                <p class="text-body-2 text-medium-emphasis mb-0">Create a strong new password for your account</p>
            </div>

            <form @submit.prevent="submit">
                <div class="mb-5">
                    <label class="auth-label text-caption font-weight-bold d-block mb-1">Email Address</label>
                    <v-text-field
                        id="input-reset-email"
                        class="readonly-email-field"
                        v-model="form.email"
                        type="email"
                        prepend-inner-icon="mdi-email-outline"
                        variant="outlined"
                        hide-details="auto"
                        :error-messages="form.errors.email"
                        readonly
                    ></v-text-field>
                </div>

                <div class="mb-5">
                    <label class="auth-label text-caption font-weight-bold d-block mb-1">New Password</label>
                    <v-text-field
                        id="input-reset-password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Enter new password"
                        prepend-inner-icon="mdi-lock-outline"
                        :append-inner-icon="showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                        variant="outlined"
                        hide-details="auto"
                        :error-messages="form.errors.password"
                        required
                        autofocus
                        autocomplete="new-password"
                        @click:append-inner="showPassword = !showPassword"
                    ></v-text-field>
                </div>

                <div class="mb-4">
                    <label class="auth-label text-caption font-weight-bold d-block mb-1">Confirm New Password</label>
                    <v-text-field
                        id="input-reset-confirm"
                        v-model="form.password_confirmation"
                        :type="showConfirm ? 'text' : 'password'"
                        placeholder="Repeat new password"
                        prepend-inner-icon="mdi-lock-check-outline"
                        :append-inner-icon="showConfirm ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                        variant="outlined"
                        hide-details="auto"
                        :error-messages="form.errors.password_confirmation"
                        required
                        autocomplete="new-password"
                        @click:append-inner="showConfirm = !showConfirm"
                    ></v-text-field>
                </div>

                <div
                    v-if="form.password && form.password_confirmation"
                    class="password-hint mb-5 d-flex align-center ga-2"
                    :class="{ 'is-match': form.password === form.password_confirmation, 'is-error': form.password !== form.password_confirmation }"
                >
                    <v-icon
                        :icon="form.password === form.password_confirmation ? 'mdi-check-circle-outline' : 'mdi-close-circle-outline'"
                        size="small"
                    ></v-icon>
                    <span class="text-caption font-weight-bold">
                        {{ form.password === form.password_confirmation ? 'Passwords match' : 'Passwords do not match' }}
                    </span>
                </div>

                <v-btn
                    id="btn-reset-password"
                    type="submit"
                    block
                    height="48"
                    color="primary"
                    class="text-none font-weight-bold text-body-1"
                    rounded="lg"
                    :loading="form.processing"
                    :disabled="form.processing"
                >
                    Reset Password
                    <v-icon icon="mdi-check" size="small" class="ml-2"></v-icon>
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

.password-hint {
    border-radius: 12px;
    padding: 8px 10px;
}

.password-hint.is-match {
    color: rgb(var(--v-theme-primary));
    background: rgba(var(--v-theme-primary), 0.07);
}

.password-hint.is-error {
    color: rgb(var(--v-theme-error));
    background: rgba(var(--v-theme-error), 0.07);
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

.readonly-email-field :deep(.v-field) {
    background: #f2f4f3;
    color: rgba(var(--v-theme-on-surface), 0.64);
}

.readonly-email-field :deep(.v-field__outline) {
    color: rgba(var(--v-border-color), 0.32);
}

.readonly-email-field :deep(.v-field__input),
.readonly-email-field :deep(.v-icon) {
    color: rgba(var(--v-theme-on-surface), 0.56);
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

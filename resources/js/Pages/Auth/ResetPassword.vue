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
const showConfirm  = ref(false);

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <!-- Back to Login (fixed bottom-left) -->
        <div class="back-home-wrapper">
            <Link :href="route('login')" class="back-home-link">
                <v-icon icon="mdi-arrow-left" size="small"></v-icon>
                Back to Login
            </Link>
        </div>

        <v-card class="pa-8 pa-sm-8" elevation="2" rounded="xl" color="white">
            <!-- Header -->
            <div class="text-center mb-8">
                <v-sheet color="#386641" rounded="lg" width="48" height="48" class="d-flex align-center justify-center mx-auto mb-4">
                    <v-icon icon="mdi-shield-key-outline" color="white" size="24"></v-icon>
                </v-sheet>
                <h1 class="text-h4 font-weight-medium mb-1">Reset Password</h1>
                <p class="text-body-2 mb-0" style="color: #666; letter-spacing: 0.2px;">
                    Create a strong new password for your account
                </p>
            </div>

            <form @submit.prevent="submit">
                <!-- Email (read-only, prefilled) -->
                <div class="mb-5">
                    <label class="text-caption font-weight-bold d-block mb-1" style="color: #444;">Email Address</label>
                    <v-text-field
                        id="input-reset-email"
                        v-model="form.email"
                        type="email"
                        prepend-inner-icon="mdi-email-outline"
                        variant="outlined"
                        hide-details="auto"
                        :error-messages="form.errors.email"
                        readonly
                        bg-color="#f9f9f9"
                        rounded="lg"
                    ></v-text-field>
                </div>

                <!-- New Password -->
                <div class="mb-5">
                    <label class="text-caption font-weight-bold d-block mb-1" style="color: #444;">New Password</label>
                    <v-text-field
                        id="input-reset-password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Enter new password"
                        prepend-inner-icon="mdi-lock-outline"
                        :append-inner-icon="showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                        @click:append-inner="showPassword = !showPassword"
                        variant="outlined"
                        hide-details="auto"
                        :error-messages="form.errors.password"
                        required
                        autofocus
                        autocomplete="new-password"
                        bg-color="transparent"
                        rounded="lg"
                    ></v-text-field>
                </div>

                <!-- Confirm Password -->
                <div class="mb-7">
                    <label class="text-caption font-weight-bold d-block mb-1" style="color: #444;">Confirm New Password</label>
                    <v-text-field
                        id="input-reset-confirm"
                        v-model="form.password_confirmation"
                        :type="showConfirm ? 'text' : 'password'"
                        placeholder="Repeat new password"
                        prepend-inner-icon="mdi-lock-check-outline"
                        :append-inner-icon="showConfirm ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                        @click:append-inner="showConfirm = !showConfirm"
                        variant="outlined"
                        hide-details="auto"
                        :error-messages="form.errors.password_confirmation"
                        required
                        autocomplete="new-password"
                        bg-color="transparent"
                        rounded="lg"
                    ></v-text-field>
                </div>

                <!-- Password match hint -->
                <div
                    v-if="form.password && form.password_confirmation"
                    class="mb-5 d-flex align-center gap-2"
                    :style="{ color: form.password === form.password_confirmation ? '#2E6B3B' : '#c62828' }"
                >
                    <v-icon
                        :icon="form.password === form.password_confirmation ? 'mdi-check-circle-outline' : 'mdi-close-circle-outline'"
                        size="small"
                    ></v-icon>
                    <span class="text-caption font-weight-medium">
                        {{ form.password === form.password_confirmation ? 'Passwords match' : 'Passwords do not match' }}
                    </span>
                </div>

                <!-- Submit Button -->
                <v-btn
                    id="btn-reset-password"
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
                    Reset Password
                    <v-icon icon="mdi-check" size="small" style="margin-left: 8px;"></v-icon>
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

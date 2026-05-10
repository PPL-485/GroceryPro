<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

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

        <v-card class="pa-8" elevation="2" rounded="xl" color="white">
            <div class="text-center mb-8">
                <v-sheet color="#B26B43" rounded="lg" width="48" height="48" class="d-flex align-center justify-center mx-auto mb-4">
                    <v-icon icon="mdi-lock-reset" color="white" size="24"></v-icon>
                </v-sheet>
                <h1 class="text-h4 font-weight-medium mb-1">Forgot Password?</h1>
                <p class="text-body-2 mb-0" style="color: #666; letter-spacing: 0.2px;">Enter your email to reset your password</p>
            </div>

            <v-alert
                v-if="status"
                type="success"
                variant="tonal"
                class="mb-6"
                density="comfortable"
            >
                {{ status }}
            </v-alert>

            <p class="text-body-2 mb-6" style="color: #666;">
                Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
            </p>

            <form @submit.prevent="submit">
                <div class="mb-6">
                    <label class="text-caption font-weight-bold d-block mb-1" style="color: #444;">Email Address</label>
                    <v-text-field
                        v-model="form.email"
                        placeholder="Enter your email"
                        prepend-inner-icon="mdi-email-outline"
                        variant="outlined"
                        hide-details="auto"
                        :error-messages="form.errors.email"
                        required
                        autofocus
                        bg-color="transparent"
                        rounded="lg"
                    ></v-text-field>
                </div>

                <v-btn
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
                </v-btn>
            </form>
        </v-card>
    </GuestLayout>
</template>

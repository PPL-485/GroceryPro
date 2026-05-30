<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <v-card class="pa-8 pa-sm-8" elevation="2" rounded="xl" color="white">
            <div class="text-center mb-8">
                <v-sheet color="#386641" rounded="lg" width="48" height="48" class="d-flex align-center justify-center mx-auto mb-4">
                    <v-icon icon="mdi-cart-outline" color="white" size="24"></v-icon>
                </v-sheet>
                <h1 class="text-h4 font-weight-medium mb-1">Welcome Back</h1>
                <p class="text-body-2 mb-0" style="color: #666; letter-spacing: 0.2px;">Sign in to your inventory management system</p>
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

            <form @submit.prevent="submit">
                <div class="mb-5">
                    <label class="text-caption font-weight-bold d-block mb-1" style="color: #444;">Email</label>
                    <v-text-field
                        id="input-email"
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

                <div class="mb-2">
                    <label class="text-caption font-weight-bold d-block mb-1" style="color: #444;">Password</label>
                    <v-text-field
                        id="input-password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Enter your password"
                        prepend-inner-icon="mdi-lock-outline"
                        :append-inner-icon="showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                        @click:append-inner="showPassword = !showPassword"
                        variant="outlined"
                        hide-details="auto"
                        :error-messages="form.errors.password"
                        required
                        rounded="lg"
                    ></v-text-field>
                </div>

                <div class="d-flex justify-end mb-6">
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-caption font-weight-medium text-decoration-none"
                        style="color: #B26B43;"
                    >
                        Forgot password?
                    </Link>
                </div>

                <v-btn
                    id="btn-login"
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
                    Sign In
                </v-btn>
            </form>
        </v-card>
    </GuestLayout>
</template>

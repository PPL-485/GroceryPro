<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirm = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <v-card class="pa-8" elevation="2" rounded="xl" color="white">
            <div class="text-center mb-8">
                <v-sheet color="#2E6B3B" rounded="lg" width="48" height="48" class="d-flex align-center justify-center mx-auto mb-4">
                    <v-icon icon="mdi-account-plus-outline" color="white" size="24"></v-icon>
                </v-sheet>
                <h1 class="text-h4 font-weight-medium mb-1">Create Account</h1>
                <p class="text-body-2 mb-0" style="color: #666; letter-spacing: 0.2px;">Join our inventory management system</p>
            </div>

            <form @submit.prevent="submit">
                <div class="mb-5">
                    <label class="text-caption font-weight-bold d-block mb-1" style="color: #444;">Full Name</label>
                    <v-text-field
                        v-model="form.name"
                        placeholder="Enter your full name"
                        prepend-inner-icon="mdi-account-outline"
                        variant="outlined"
                        hide-details="auto"
                        :error-messages="form.errors.name"
                        required
                        autofocus
                        bg-color="transparent"
                        rounded="lg"
                    ></v-text-field>
                </div>

                <div class="mb-5">
                    <label class="text-caption font-weight-bold d-block mb-1" style="color: #444;">Email</label>
                    <v-text-field
                        v-model="form.email"
                        placeholder="Enter your email"
                        prepend-inner-icon="mdi-email-outline"
                        variant="outlined"
                        hide-details="auto"
                        :error-messages="form.errors.email"
                        required
                        bg-color="transparent"
                        rounded="lg"
                    ></v-text-field>
                </div>

                <div class="mb-5">
                    <label class="text-caption font-weight-bold d-block mb-1" style="color: #444;">Password</label>
                    <v-text-field
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Create a password"
                        prepend-inner-icon="mdi-lock-outline"
                        :append-inner-icon="showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                        @click:append-inner="showPassword = !showPassword"
                        variant="outlined"
                        hide-details="auto"
                        :error-messages="form.errors.password"
                        required
                        bg-color="transparent"
                        rounded="lg"
                    ></v-text-field>
                </div>

                <div class="mb-6">
                    <label class="text-caption font-weight-bold d-block mb-1" style="color: #444;">Confirm Password</label>
                    <v-text-field
                        v-model="form.password_confirmation"
                        :type="showPasswordConfirm ? 'text' : 'password'"
                        placeholder="Confirm your password"
                        prepend-inner-icon="mdi-lock-check-outline"
                        :append-inner-icon="showPasswordConfirm ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                        @click:append-inner="showPasswordConfirm = !showPasswordConfirm"
                        variant="outlined"
                        hide-details="auto"
                        :error-messages="form.errors.password_confirmation"
                        required
                        bg-color="transparent"
                        rounded="lg"
                    ></v-text-field>
                </div>

                <v-btn
                    type="submit"
                    block
                    height="48"
                    color="#2E6B3B"
                    class="text-none font-weight-bold text-body-1 mb-4"
                    rounded="lg"
                    :loading="form.processing"
                    :disabled="form.processing"
                    style="letter-spacing: 0.5px; box-shadow: 0 4px 12px rgba(46, 107, 59, 0.2); color: white;"
                >
                    Create Account
                </v-btn>

                <div class="text-center">
                    <span class="text-caption" style="color: #666;">Already have an account?</span>
                    <Link
                        :href="route('login')"
                        class="text-caption font-weight-medium text-decoration-none d-block"
                        style="color: #2E6B3B;"
                    >
                        Sign in here
                    </Link>
                </div>
            </form>
        </v-card>
    </GuestLayout>
</template>

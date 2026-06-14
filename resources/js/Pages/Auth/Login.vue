<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const snackbar = ref(false);

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

        <!-- Back to Home (fixed bottom-left) -->
        <div class="back-home-wrapper">
            <Link href="/" class="back-home-link">
                <v-icon icon="mdi-arrow-left" size="small"></v-icon>
                Back to Home
            </Link>
        </div>

        <v-card class="pa-8 pa-sm-8" elevation="2" rounded="xl" color="white">
            <div class="text-center mb-8">
                <v-sheet color="#386641" rounded="lg" width="48" height="48" class="d-flex align-center justify-center mx-auto mb-4">
                    <v-icon icon="mdi-cart-outline" color="white" size="24"></v-icon>
                </v-sheet>
                <h1 class="text-h4 font-weight-medium mb-1">Welcome Back</h1>
                <p class="text-body-2 mb-0" style="color: #666; letter-spacing: 0;">Sign in to manage checkout, stock, and reports</p>
            </div>

        <!-- Success Snackbar (after password reset) -->
        <v-snackbar
            v-model="snackbar"
            color="#2E6B3B"
            rounded="lg"
            timeout="5000"
            location="top"
            elevation="4"
        >
            <div class="d-flex align-center gap-3">
                <v-icon icon="mdi-check-circle" color="white"></v-icon>
                <span style="font-weight: 600;">{{ status }}</span>
            </div>
            <template #actions>
                <v-btn icon="mdi-close" size="small" color="white" variant="text" @click="snackbar = false"></v-btn>
            </template>
        </v-snackbar>

            <form @submit.prevent="submit">
                <div class="mb-5">
                    <label class="text-caption font-weight-bold d-block mb-1" style="color: #444;">Email Address</label>
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
                        placeholder="Enter account password"
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
                    style="letter-spacing: 0; box-shadow: 0 4px 12px rgba(178, 107, 67, 0.2); color: white;"
                >
                    Sign In
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

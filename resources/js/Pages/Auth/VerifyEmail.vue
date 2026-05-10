<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <v-card class="pa-8" elevation="2" rounded="xl" color="white">
            <div class="text-center mb-8">
                <v-sheet color="#2E6B3B" rounded="lg" width="48" height="48" class="d-flex align-center justify-center mx-auto mb-4">
                    <v-icon icon="mdi-email-check-outline" color="white" size="24"></v-icon>
                </v-sheet>
                <h1 class="text-h4 font-weight-medium mb-1">Verify Email</h1>
                <p class="text-body-2 mb-0" style="color: #666; letter-spacing: 0.2px;">Check your inbox for verification</p>
            </div>

            <v-alert
                v-if="verificationLinkSent"
                type="success"
                variant="tonal"
                class="mb-6"
                density="comfortable"
            >
                A new verification link has been sent to the email address you provided during registration.
            </v-alert>

            <p class="text-body-2 mb-6" style="color: #666;">
                Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
            </p>

            <form @submit.prevent="submit">
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
                    Resend Verification Email
                </v-btn>
            </form>

            <div class="text-center">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-caption font-weight-medium text-decoration-none d-block"
                    style="color: #B26B43; background: none; border: none; cursor: pointer;"
                >
                    Sign Out
                </Link>
            </div>
        </v-card>
    </GuestLayout>
</template>

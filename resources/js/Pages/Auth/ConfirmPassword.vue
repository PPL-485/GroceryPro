<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
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

        <v-card class="pa-8" elevation="2" rounded="xl" color="white">
            <div class="text-center mb-8">
                <v-sheet color="#B26B43" rounded="lg" width="48" height="48" class="d-flex align-center justify-center mx-auto mb-4">
                    <v-icon icon="mdi-lock-check" color="white" size="24"></v-icon>
                </v-sheet>
                <h1 class="text-h4 font-weight-medium mb-1">Confirm Password</h1>
                <p class="text-body-2 mb-0" style="color: #666; letter-spacing: 0.2px;">This is a secure area</p>
            </div>

            <p class="text-body-2 mb-6" style="color: #666;">
                This is a secure area of the application. Please confirm your password before continuing.
            </p>

            <form @submit.prevent="submit">
                <div class="mb-6">
                    <label class="text-caption font-weight-bold d-block mb-1" style="color: #444;">Password</label>
                    <v-text-field
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
                    Confirm
                </v-btn>
            </form>
        </v-card>
    </GuestLayout>
</template>

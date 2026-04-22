<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Array,
});

const roles = ['admin', 'cashier'];
const snackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');

const updateRole = (user, newRole) => {
    router.put(route('users.update-role', user.id), {
        role: newRole,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            snackbarMessage.value = 'User role updated successfully.';
            snackbarColor.value = 'success';
            snackbar.value = true;
        },
        onError: () => {
            snackbarMessage.value = 'Failed to update user role.';
            snackbarColor.value = 'error';
            snackbar.value = true;
        }
    });
};
</script>

<template>
    <Head title="User Management" />

    <AuthenticatedLayout>
        <template #header-title>
            User Management
        </template>
        
        <template #header-description>
            <p class="text-sm text-gray-500">
                Manage users and their roles
            </p>
        </template>
        
        <div class="overflow-hidden shadow-sm sm:rounded-lg">
            <v-card>
                <v-card-title class="px-6 pt-6">
                    <h2 class="text-h6 font-weight-medium">Users List</h2>
                </v-card-title>
                
                <v-card-text>
                    <v-table>
                        <thead>
                            <tr>
                                <th class="text-left">Name</th>
                                <th class="text-left">Email</th>
                                <th class="text-left">Phone</th>
                                <th class="text-left">Status</th>
                                <th class="text-left">Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id">
                                <td>{{ user.name }}</td>
                                <td>{{ user.email }}</td>
                                <td>{{ user.phone || '-' }}</td>
                                <td>
                                    <v-chip
                                        :color="user.status === 'active' ? 'success' : 'error'"
                                        size="small"
                                    >
                                        {{ user.status }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-select
                                        v-model="user.role"
                                        :items="roles"
                                        density="compact"
                                        variant="outlined"
                                        hide-details
                                        @update:modelValue="(newRole) => updateRole(user, newRole)"
                                        class="mt-2 mb-2"
                                        style="max-width: 150px"
                                    ></v-select>
                                </td>
                            </tr>
                            <tr v-if="!users || users.length === 0">
                                <td colspan="5" class="text-center text-gray-500 py-4">
                                    No users found.
                                </td>
                            </tr>
                        </tbody>
                    </v-table>
                </v-card-text>
            </v-card>

            <v-snackbar
                v-model="snackbar"
                :color="snackbarColor"
                timeout="3000"
            >
                {{ snackbarMessage }}
                
                <template v-slot:actions>
                    <v-btn
                        color="white"
                        variant="text"
                        @click="snackbar = false"
                    >
                        Close
                    </v-btn>
                </template>
            </v-snackbar>
        </div>
    </AuthenticatedLayout>
</template>

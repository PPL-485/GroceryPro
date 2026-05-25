<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    users: Array,
});

const roles = ['admin', 'cashier'];
const snackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');
const search = ref('');

const isAddUserModalOpen = ref(false);
const deleteDialog = ref(false);
const userToDelete = ref(null);
const userForm = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    role: 'cashier',
    status: 'active',
});

const submitUser = () => {
    userForm.post(route('users.store'), {
        onSuccess: () => {
            isAddUserModalOpen.value = false;
            userForm.reset();
            snackbarMessage.value = 'User created successfully.';
            snackbarColor.value = 'success';
            snackbar.value = true;
        },
        onError: () => {
            snackbarMessage.value = 'Failed to create user. Please check the inputs.';
            snackbarColor.value = 'error';
            snackbar.value = true;
        }
    });
};

const filteredUsers = computed(() => {
    if (!search.value) {
        return props.users;
    }
    const searchTerm = search.value.toLowerCase();
    return props.users.filter(user => 
        user.name.toLowerCase().includes(searchTerm)
    );
});

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

const toggleStatus = (user) => {
    router.put(route('users.update-status', user.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            snackbarMessage.value = 'User status updated successfully.';
            snackbarColor.value = 'success';
            snackbar.value = true;
        },
        onError: () => {
            snackbarMessage.value = 'Failed to update user status.';
            snackbarColor.value = 'error';
            snackbar.value = true;
        }
    });
};

const confirmDeleteUser = (user) => {
    userToDelete.value = user;
    deleteDialog.value = true;
};

const executeDelete = () => {
    if (!userToDelete.value) return;

    router.delete(route('users.destroy', userToDelete.value.id), {
        preserveScroll: true,
        onSuccess: (page) => {
            deleteDialog.value = false;
            userToDelete.value = null;
            if (page.props.flash && page.props.flash.error) {
                snackbarMessage.value = page.props.flash.error;
                snackbarColor.value = 'error';
            } else {
                snackbarMessage.value = 'User deleted successfully.';
                snackbarColor.value = 'success';
            }
            snackbar.value = true;
        },
        onError: () => {
            deleteDialog.value = false;
            snackbarMessage.value = 'Failed to delete user.';
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
        
        <v-container fluid class="pa-0 mt-4">
            <!-- Search and Action Buttons -->
            <v-row class="mb-4" align="center">
                <v-col cols="12" md="6">
                    <v-text-field
                        placeholder="Search users..."
                        prepend-inner-icon="mdi-magnify"
                        variant="outlined"
                        rounded="lg"
                        v-model="search"
                        density="compact"
                        hide-details
                    ></v-text-field>
                </v-col>
                <v-spacer></v-spacer>
                <v-col cols="auto">
                    <v-btn
                        color="primary"
                        rounded="lg"
                        class="text-none"
                        @click="isAddUserModalOpen = true"
                    >
                        <v-icon start size="small">mdi-plus</v-icon>
                        Add User
                    </v-btn>
                </v-col>
            </v-row>

            <v-card class="rounded-xl border" elevation="0">
                <v-table hover>
                        <thead>
                            <tr>
                                <th class="text-left">Name</th>
                                <th class="text-left">Email</th>
                                <th class="text-left">Phone</th>
                                <th class="text-left">Status</th>
                                <th class="text-left">Role</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in filteredUsers" :key="user.id">
                                <td>{{ user.name }}</td>
                                <td>{{ user.email }}</td>
                                <td>{{ user.phone || '-' }}</td>
                                <td>
                                    <div class="d-flex align-center">
                                        <v-switch
                                            :model-value="user.status === 'active'"
                                            color="success"
                                            base-color="error"
                                            hide-details
                                            density="compact"
                                            @change="toggleStatus(user)"
                                        ></v-switch>
                                    </div>
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
                                <td class="text-center">
                                    <v-btn
                                        icon="mdi-trash-can-outline"
                                        color="#C87A54"
                                        variant="text"
                                        density="comfortable"
                                        @click="confirmDeleteUser(user)"
                                        title="Delete User"
                                    ></v-btn>
                                </td>
                            </tr>
                            <tr v-if="!filteredUsers || filteredUsers.length === 0">
                                <td colspan="6" class="text-center text-gray-500 py-4">
                                    No users found.
                                </td>
                            </tr>
                        </tbody>
                    </v-table>
            </v-card>

            <v-dialog v-model="isAddUserModalOpen" max-width="500">
                <v-card class="rounded-xl">
                    <v-card-title class="pa-4 font-weight-bold d-flex justify-space-between align-center border-b">
                        Add New User
                        <v-btn icon="mdi-close" variant="text" size="small" @click="isAddUserModalOpen = false"></v-btn>
                    </v-card-title>
                    <v-card-text class="pa-4">
                        <v-form @submit.prevent="submitUser">
                            <v-row>
                                <v-col cols="12" class="pb-0">
                                    <div class="mb-2 text-subtitle-2 font-weight-medium">Name</div>
                                    <v-text-field
                                        v-model="userForm.name"
                                        variant="outlined"
                                        density="comfortable"
                                        placeholder="Enter name"
                                        :error-messages="userForm.errors.name"
                                        rounded="lg"
                                        required
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" class="pb-0">
                                    <div class="mb-2 text-subtitle-2 font-weight-medium">Email</div>
                                    <v-text-field
                                        v-model="userForm.email"
                                        type="email"
                                        variant="outlined"
                                        density="comfortable"
                                        placeholder="Enter email"
                                        :error-messages="userForm.errors.email"
                                        rounded="lg"
                                        required
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" class="pb-0">
                                    <div class="mb-2 text-subtitle-2 font-weight-medium">Phone (Optional)</div>
                                    <v-text-field
                                        v-model="userForm.phone"
                                        variant="outlined"
                                        density="comfortable"
                                        placeholder="Enter phone number"
                                        :error-messages="userForm.errors.phone"
                                        rounded="lg"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" class="pb-0">
                                    <div class="mb-2 text-subtitle-2 font-weight-medium">Password</div>
                                    <v-text-field
                                        v-model="userForm.password"
                                        type="password"
                                        variant="outlined"
                                        density="comfortable"
                                        placeholder="Enter password"
                                        :error-messages="userForm.errors.password"
                                        rounded="lg"
                                        required
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="6" class="pb-0">
                                    <div class="mb-2 text-subtitle-2 font-weight-medium">Role</div>
                                    <v-select
                                        v-model="userForm.role"
                                        :items="roles"
                                        variant="outlined"
                                        density="comfortable"
                                        :error-messages="userForm.errors.role"
                                        rounded="lg"
                                        required
                                    ></v-select>
                                </v-col>
                                <v-col cols="12" sm="6" class="pb-0">
                                    <div class="mb-2 text-subtitle-2 font-weight-medium">Status</div>
                                    <v-select
                                        v-model="userForm.status"
                                        :items="['active', 'inactive']"
                                        variant="outlined"
                                        density="comfortable"
                                        :error-messages="userForm.errors.status"
                                        rounded="lg"
                                        required
                                    ></v-select>
                                </v-col>
                            </v-row>
                        </v-form>
                    </v-card-text>
                    <v-card-actions class="pa-4 pt-4 border-t mt-4">
                        <v-spacer></v-spacer>
                        <v-btn
                            variant="tonal"
                            @click="isAddUserModalOpen = false"
                            rounded="lg"
                            class="px-4 text-none"
                        >
                            Cancel
                        </v-btn>
                        <v-btn
                            color="primary"
                            @click="submitUser"
                            :loading="userForm.processing"
                            rounded="lg"
                            class="px-4 text-none"
                            variant="flat"
                        >
                            Save
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <!-- Delete Confirmation Dialog -->
            <v-dialog v-model="deleteDialog" max-width="400">
                <v-card class="rounded-xl">
                    <v-card-text class="pa-6 text-center">
                        <v-icon size="64" color="error" class="mb-4">mdi-alert-circle-outline</v-icon>
                        <div class="text-h6 font-weight-bold mb-2">Delete User?</div>
                        <div class="text-body-2 text-grey-darken-1">
                            Are you sure you want to delete <strong>{{ userToDelete?.name }}</strong>? This action cannot be undone.
                        </div>
                    </v-card-text>
                    
                    <v-card-actions class="pa-4 pt-0 justify-center">
                        <v-btn
                            variant="tonal"
                            @click="deleteDialog = false"
                            rounded="lg"
                            class="px-6 text-none"
                        >
                            Cancel
                        </v-btn>
                        <v-btn
                            color="error"
                            @click="executeDelete"
                            rounded="lg"
                            class="px-6 text-none"
                            variant="flat"
                        >
                            Yes, Delete
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

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
        </v-container>
    </AuthenticatedLayout>
</template>

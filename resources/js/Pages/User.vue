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

const deleteUser = (user) => {
    if (confirm(`Are you sure you want to delete user ${user.name}?`)) {
        router.delete(route('users.destroy', user.id), {
            preserveScroll: true,
            onSuccess: (page) => {
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
                snackbarMessage.value = 'Failed to delete user.';
                snackbarColor.value = 'error';
                snackbar.value = true;
            }
        });
    }
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
                <v-card-text>
                    <div class="d-flex align-center mb-4 gap-4">
                        <v-text-field
                            label="Search"
                            prepend-inner-icon="mdi-magnify"
                            variant="outlined"
                            rounded="lg"
                            v-model="search"
                            density="comfortable"
                            clearable
                            hide-details
                            class="flex-grow-1"
                        ></v-text-field>
                        <v-btn
                            color="primary"
                            prepend-icon="mdi-plus"
                            @click="isAddUserModalOpen = true"
                            height="48"
                            class="ml-4"
                        >
                            Add User
                        </v-btn>
                    </div>
                    <v-table>
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
                                            hide-details
                                            density="compact"
                                            @change="toggleStatus(user)"
                                        ></v-switch>
                                        <v-chip
                                            :color="user.status === 'active' ? 'success' : 'error'"
                                            size="small"
                                            class="ml-2"
                                        >
                                            {{ user.status }}
                                        </v-chip>
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
                                        icon="mdi-delete"
                                        color="error"
                                        variant="text"
                                        density="comfortable"
                                        @click="deleteUser(user)"
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
                </v-card-text>
            </v-card>

            <v-dialog v-model="isAddUserModalOpen" max-width="500px">
                <v-card>
                    <v-card-title>
                        <span class="text-h5">Add New User</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12">
                                    <v-text-field
                                        v-model="userForm.name"
                                        label="Name"
                                        :error-messages="userForm.errors.name"
                                        required
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-text-field
                                        v-model="userForm.email"
                                        label="Email"
                                        type="email"
                                        :error-messages="userForm.errors.email"
                                        required
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-text-field
                                        v-model="userForm.phone"
                                        label="Phone (Optional)"
                                        :error-messages="userForm.errors.phone"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-text-field
                                        v-model="userForm.password"
                                        label="Password"
                                        type="password"
                                        :error-messages="userForm.errors.password"
                                        required
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-select
                                        v-model="userForm.role"
                                        :items="roles"
                                        label="Role"
                                        :error-messages="userForm.errors.role"
                                        required
                                    ></v-select>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-select
                                        v-model="userForm.status"
                                        :items="['active', 'inactive']"
                                        label="Status"
                                        :error-messages="userForm.errors.status"
                                        required
                                    ></v-select>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="blue-darken-1" variant="text" @click="isAddUserModalOpen = false">
                            Cancel
                        </v-btn>
                        <v-btn color="blue-darken-1" variant="text" @click="submitUser" :loading="userForm.processing">
                            Save
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
        </div>
    </AuthenticatedLayout>
</template>

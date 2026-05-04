<script setup>
import { ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const user = usePage().props.auth.user;

const tab = ref('info_settings');
const lowStockAlerts = ref(true);

const form = useForm({
    name: user.name,
    phone: user.phone || '',
    email: user.email,
});

const submitProfile = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset('password', 'password_confirmation');
            }
            if (passwordForm.errors.current_password) {
                passwordForm.reset('current_password');
            }
        },
    });
};

const fileInput = ref(null);
const restoreForm = useForm({
    file: null,
});

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    if (confirm('Are you sure you want to restore the database? This will overwrite all current data and cannot be undone!')) {
        restoreForm.file = file;
        restoreForm.post(route('restore'), {
            preserveScroll: true,
            onSuccess: () => {
                alert('Database restored successfully!');
                event.target.value = null;
            },
            onError: () => {
                alert('Failed to restore database.');
                event.target.value = null;
            }
        });
    } else {
        event.target.value = null;
    }
};
</script>

<template>
    <Head title="store_info" />

    <AuthenticatedLayout>
        <!-- Provide title/description slots to the layout's AppBar if desired, 
             but we will render a prominent header inside the page content to match the design. -->
        <template #header-title>Settings</template>
        
        <div class="px-2 pb-6 max-w-5xl mx-auto">
            <!-- Tabs -->
            <div class="px-2">
                <v-tabs
                    v-model="tab"
                    color="primary"
                    align-tabs="start"
                    class="mb-8 custom-tabs rounded-xl"
                    height="48"
                >
                    <v-tab value="info_settings" class="text-none custom-tab" rounded="xl" :ripple="false">Info Settings</v-tab>
                    <v-tab value="notifications" class="text-none custom-tab" rounded="xl" :ripple="false">Notifications</v-tab>
                    <v-tab value="security" class="text-none custom-tab" rounded="xl" :ripple="false">Security</v-tab>
                    <v-tab value="appearance" class="text-none custom-tab" rounded="xl" :ripple="false">Appearance</v-tab>
                    <v-tab value="system" class="text-none custom-tab" rounded="xl" :ripple="false">System</v-tab>
                </v-tabs>
            </div>

            <!-- Tab Windows -->
            <v-window v-model="tab" class="overflow-visible px-2">
                <!-- Info Settings Tab Content -->
                <v-window-item value="info_settings">
                    <v-card hover flat class="rounded-xl border">
                        <form @submit.prevent="submitProfile">
                            <!-- Card Header -->
                            <div class="d-flex align-center px-8 pt-8 pb-4">
                                <v-icon icon="mdi-account-outline" size="28" class="mr-4" color="primary"></v-icon>
                                <span class="text-h6 font-weight-medium text-primary">Profil Informasi Pribadi</span>
                            </div>

                            <!-- Card Content -->
                            <v-card-text class="px-8 pb-4 pt-4">
                                <v-row>
                                    <v-col cols="12" md="6" class="pb-2">
                                        <div class="text-subtitle-2 font-weight-medium mb-2">Full Name</div>
                                        <v-text-field
                                            v-model="form.name"
                                            variant="outlined"
                                            density="comfortable"
                                            hide-details="auto"
                                            rounded="lg"
                                            :error-messages="form.errors.name"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="6" class="pb-2">
                                        <div class="text-subtitle-2 font-weight-medium mb-2">Phone Number</div>
                                        <v-text-field
                                            v-model="form.phone"
                                            variant="outlined"
                                            density="comfortable"
                                            hide-details="auto"
                                            rounded="lg"
                                            :error-messages="form.errors.phone"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="12" md="6" class="pt-2 pb-4">
                                        <div class="text-subtitle-2 font-weight-medium mb-2">Email</div>
                                        <v-text-field
                                            v-model="form.email"
                                            variant="outlined"
                                            density="comfortable"
                                            hide-details="auto"
                                            rounded="lg"
                                            :error-messages="form.errors.email"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="6" class="pt-2 pb-4">
                                        <div class="text-subtitle-2 font-weight-medium mb-2">Role</div>
                                        <v-text-field
                                            :model-value="user.role"
                                            variant="outlined"
                                            density="comfortable"
                                            hide-details="auto"
                                            rounded="lg"
                                            readonly
                                            class="text-capitalize"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                            </v-card-text>

                            <!-- Divider -->
                            <v-divider class="mx-8"></v-divider>

                            <!-- Card Actions -->
                            <v-card-actions class="px-8 py-6 justify-end">
                                <v-btn
                                    variant="flat"
                                    class="text-none px-6 mr-3 rounded-lg border-grey-lighten-2 font-weight-medium"
                                    height="44"
                                    @click="form.reset()"
                                >
                                    Cancel
                                </v-btn>
                                <v-btn
                                    type="submit"
                                    variant="flat"
                                    color="primary"
                                    class="text-none px-6 rounded-lg text-white font-weight-medium"
                                    height="44"
                                    :loading="form.processing"
                                >
                                    Save Changes
                                </v-btn>
                            </v-card-actions>
                        </form>
                    </v-card>
                </v-window-item>

                <!-- Notifications Tab Content -->
                <v-window-item value="notifications">
                    <v-card hover flat class="rounded-xl border">
                        <!-- Card Header -->
                        <div class="d-flex align-center px-8 pt-8 pb-4">
                            <v-icon icon="mdi-bell-outline" size="28" class="mr-4" color="primary"></v-icon>
                            <span class="text-h6 font-weight-medium text-primary">Notification Settings</span>
                        </div>

                        <!-- Card Content -->
                        <v-card-text class="px-8 pb-8 pt-4">
                            <div class="d-flex align-center justify-space-between mb-2">
                                <div>
                                    <div class="text-subtitle-1 font-weight-medium">Low Stock Alerts</div>
                                    <div class="text-body-1 mt-1">Get notified when products are running low</div>
                                </div>
                                <v-switch
                                    v-model="lowStockAlerts"
                                    inset
                                    hide-details
                                    density="compact"
                                ></v-switch>
                            </div>
                        </v-card-text>

                        <!-- Divider -->
                        <v-divider class="mx-8" color="#E5E7EB"></v-divider>

                        <!-- Card Actions -->
                        <v-card-actions class="px-8 py-6 justify-end">
                            <v-btn
                                variant="flat"
                                class="text-none px-6 mr-3 rounded-lg border-grey-lighten-2 font-weight-medium"
                                height="44"
                            >
                                Reset to Default
                            </v-btn>
                            <v-btn
                                variant="flat"
                                color="primary"
                                class="text-none px-6 rounded-lg text-white font-weight-medium"
                                height="44"
                            >
                                Save Preferences
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-window-item>
                
                <v-window-item value="security">
                    <v-card hover flat class="rounded-xl border">
                        <form @submit.prevent="updatePassword">
                            <!-- Card Header -->
                            <div class="d-flex align-center px-8 pt-8 pb-4">
                                <v-icon icon="mdi-lock-outline" size="28" class="mr-4" color="primary"></v-icon>
                                <span class="text-h6 font-weight-medium text-primary">Security Settings</span>
                            </div>

                            <!-- Card Content -->
                            <v-card-text class="px-8 pb-4 pt-4">
                                <v-row>
                                    <v-col cols="12" class="pb-2">
                                        <div class="text-subtitle-2 font-weight-medium mb-2">Current Password</div>
                                        <v-text-field
                                            v-model="passwordForm.current_password"
                                            type="password"
                                            variant="outlined"
                                            density="comfortable"
                                            hide-details="auto"
                                            rounded="lg"
                                            :error-messages="passwordForm.errors.current_password"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="12" class="py-2">
                                        <div class="text-subtitle-2 font-weight-medium mb-2">New Password</div>
                                        <v-text-field
                                            v-model="passwordForm.password"
                                            type="password"
                                            variant="outlined"
                                            density="comfortable"
                                            hide-details="auto"
                                            rounded="lg"
                                            :error-messages="passwordForm.errors.password"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="12" class="pt-2 pb-4">
                                        <div class="text-subtitle-2 font-weight-medium mb-2">Confirm New Password</div>
                                        <v-text-field
                                            v-model="passwordForm.password_confirmation"
                                            type="password"
                                            variant="outlined"
                                            density="comfortable"
                                            hide-details="auto"
                                            rounded="lg"
                                            :error-messages="passwordForm.errors.password_confirmation"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                            </v-card-text>

                            <!-- Divider -->
                            <v-divider class="mx-8"></v-divider>

                            <!-- Card Actions -->
                            <v-card-actions class="px-8 py-6 justify-end">
                                <v-btn
                                    variant="flat"
                                    class="text-none px-6 mr-3 rounded-lg border-grey-lighten-2 font-weight-medium"
                                    height="44"
                                    @click="passwordForm.reset()"
                                >
                                    Cancel
                                </v-btn>
                                <v-btn
                                    type="submit"
                                    variant="flat"
                                    color="primary"
                                    class="text-none px-6 rounded-lg text-white font-weight-medium"
                                    height="44"
                                    :loading="passwordForm.processing"
                                >
                                    Update Password
                                </v-btn>
                            </v-card-actions>
                        </form>
                    </v-card>
                </v-window-item>

                <v-window-item value="appearance">
                    <v-card hover flat class="rounded-xl border pa-10 text-center text-grey">
                        <v-icon size="48" class="mb-4" color="primary">mdi-palette-outline</v-icon>
                        <div class="text-h6 text-primary">Appearance Settings</div>
                        <div class="text-body-1 mt-2" style="color: #6B7280;">Customize the look and feel.</div>
                    </v-card>
                </v-window-item>

                <v-window-item value="system">
                    <v-card hover flat class="rounded-xl border">
                        <!-- Card Header -->
                        <div class="d-flex align-center px-8 pt-8 pb-4">
                            <v-icon icon="mdi-database-export-outline" size="28" class="mr-4" color="primary"></v-icon>
                            <span class="text-h6 font-weight-medium text-primary">Database Backup</span>
                        </div>

                        <!-- Card Content -->
                        <v-card-text class="px-8 pb-8 pt-4">
                            <div class="d-flex align-center justify-space-between mb-2">
                                <div>
                                    <div class="text-subtitle-1 font-weight-medium">Export Database</div>
                                    <div class="text-body-1 mt-1" style="color: #6B7280;">Download a complete backup of your database as .sql file</div>
                                </div>
                                <a
                                    :href="route('backup')"
                                    target="_blank"
                                    class="text-decoration-none"
                                >
                                    <v-btn
                                        variant="flat"
                                        color="primary"
                                        class="text-none px-6 rounded-lg text-white font-weight-medium"
                                        height="44"
                                    >
                                        <v-icon start>mdi-download</v-icon>
                                        Backup Now
                                    </v-btn>
                                </a>
                            </div>
                        </v-card-text>

                        <!-- Divider -->
                        <v-divider class="mx-8" color="#E5E7EB"></v-divider>

                        <!-- Restore Section -->
                        <v-card-text class="px-8 pb-8 pt-4">
                            <div class="d-flex align-center justify-space-between mb-2">
                                <div>
                                    <div class="text-subtitle-1 font-weight-medium">Restore Database</div>
                                    <div class="text-body-1 mt-1" style="color: #6B7280;">Upload a .sql backup file to restore your database</div>
                                </div>
                                <div>
                                    <v-btn
                                        variant="tonal"
                                        color="primary"
                                        class="text-none px-6 rounded-lg font-weight-medium"
                                        height="44"
                                        @click="fileInput.click()"
                                        :loading="restoreForm.processing"
                                    >
                                        <v-icon start>mdi-upload</v-icon>
                                        Restore Data
                                    </v-btn>
                                    <input
                                        type="file"
                                        ref="fileInput"
                                        accept=".sql"
                                        class="d-none"
                                        @change="handleFileUpload"
                                    >
                                </div>
                            </div>
                        </v-card-text>

                        <!-- Divider -->
                        <v-divider class="mx-8" color="#E5E7EB"></v-divider>

                        <!-- Card Info -->
                        <v-card-text class="px-8 py-6">
                            <div class="d-flex align-center">
                                <v-icon size="small" color="#6B7280" class="mr-2">mdi-information-outline</v-icon>
                                <span class="text-body-2" style="color: #6B7280;">The backup file will include all tables and data from your database.</span>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-window-item>
            </v-window>
        </div>

        <!-- Tab Windows -->
        <v-window v-model="tab" class="overflow-visible px-2">

            <!-- Placeholders for other tabs -->
            <v-window-item value="store_info">
                <v-card variant="outlined" class=" rounded-xl pa-10 text-center text-grey">
                    <v-icon size="48" class="mb-4">mdi-store</v-icon>
                    <div class="text-h6">Store Info Settings</div>
                    <div class="text-body-1 mt-2">Configuration for store information.</div>
                </v-card>
            </v-window-item>

            <!-- Notifications Tab Content (From the image) -->
            <v-window-item value="notifications">
                <v-card variant="outlined" class=" rounded-xl">
                    <!-- Card Header -->
                    <div class="d-flex align-center px-8 pt-8 pb-4">
                        <v-icon icon="mdi-bell-outline" size="28" class="mr-4" color="black"></v-icon>
                        <span class="text-h6 font-weight-medium" style="color: #000;">Notification Settings</span>
                    </div>

                    <!-- Card Content -->
                    <v-card-text class="px-8 pb-8 pt-4">
                        <div class="d-flex align-center justify-space-between mb-2">
                            <div>
                                <div class="text-subtitle-1 font-weight-medium" style="color: #000;">Low Stock Alerts</div>
                                <div class="text-body-1 mt-1" style="color: #6B7280;">Get notified when products are running low</div>
                            </div>
                            <v-switch
                                v-model="lowStockAlerts"
                                color="black"
                                inset
                                hide-details
                                density="compact"
                            ></v-switch>
                        </div>
                    </v-card-text>

                    <!-- Divider -->
                    <v-divider class="mx-8 border-opacity-100" color="#E5E7EB"></v-divider>

                    <!-- Card Actions -->
                    <v-card-actions class="px-8 py-6 justify-end">
                        <v-btn
                            variant="outlined"
                            color="black"
                            class="text-none px-6 mr-3 rounded-lg border-grey-lighten-2 font-weight-medium"
                            height="44"
                        >
                            Reset to Default
                        </v-btn>
                        <v-btn
                            variant="flat"
                            color="#C67D53"
                            class="text-none px-6 rounded-lg text-white font-weight-medium"
                            height="44"
                        >
                            Save Preferences
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-window-item>

            <v-window-item value="security">
                <v-card variant="outlined" class=" rounded-xl pa-10 text-center text-grey">
                    <v-icon size="48" class="mb-4">mdi-shield-check-outline</v-icon>
                    <div class="text-h6">Security Settings</div>
                    <div class="text-body-1 mt-2">Manage security and passwords.</div>
                </v-card>
            </v-window-item>

            <v-window-item value="appearance">
                <v-card variant="outlined" class=" rounded-xl pa-10 text-center text-grey">
                    <v-icon size="48" class="mb-4">mdi-palette-outline</v-icon>
                    <div class="text-h6">Appearance Settings</div>
                    <div class="text-body-1 mt-2">Customize the look and feel.</div>
                </v-card>
            </v-window-item>

            <v-window-item value="system">
                <v-card variant="outlined" class=" rounded-xl">
                    <!-- Card Header -->
                    <div class="d-flex align-center px-8 pt-8 pb-4">
                        <v-icon icon="mdi-database-export-outline" size="28" class="mr-4"></v-icon>
                        <span class="text-h6 font-weight-medium">Database Backup</span>
                    </div>

                    <!-- Card Content -->
                    <v-card-text class="px-8 pb-8 pt-4">
                        <div class="d-flex align-center justify-space-between mb-2">
                            <div>
                                <div class="text-subtitle-1 font-weight-medium">Export Database</div>
                                <div class="text-body-1 mt-1">Download a complete backup of your database as .sql file</div>
                            </div>
                            <a
                                :href="route('backup')"
                                target="_blank"
                                class="text-decoration-none"
                            >
                                <v-btn
                                    variant="flat"
                                    color="#C67D53"
                                    class="text-none px-6 rounded-lg text-white font-weight-medium"
                                    height="44"
                                >
                                    <v-icon start>mdi-download</v-icon>
                                    Backup Now
                                </v-btn>
                            </a>
                        </div>
                    </v-card-text>

                    <!-- Divider -->
                    <v-divider class="mx-8 border-opacity-100" color="#E5E7EB"></v-divider>

                    <!-- Card Info -->
                    <v-card-text class="px-8 py-6">
                        <div class="d-flex align-center">
                            <v-icon size="small" color="info" class="mr-2">mdi-information-outline</v-icon>
                            <span class="text-body-2" style="color: #6B7280;">The backup file will include all tables and data from your database.</span>
                        </div>
                    </v-card-text>
                </v-card>
            </v-window-item>
        </v-window>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Settings card styling */
/* .settings-card {
    border-color: #E5E7EB;
    background-color: #FFFFFF;
} */
</style>

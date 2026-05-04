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
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <!-- Provide title/description slots to the layout's AppBar if desired, 
             but we will render a prominent header inside the page content to match the design. -->
        <template #header-title>Settings</template>
        
        <div class="px-2 pb-6 max-w-5xl mx-auto">
            <!-- Page Header -->
            <div class="mb-8 px-2">
                <h1 class="text-h3 font-weight-regular mb-2" style="font-family: Georgia, serif !important; color: #000;">Settings</h1>
                <p class="text-subtitle-1" style="color: #4A5D53;">Configure your store settings and preferences</p>
            </div>

            <!-- Tabs -->
            <div class="px-2">
                <v-tabs
                    v-model="tab"
                    color="primary"
                    align-tabs="start"
                    class="mb-8 custom-tabs rounded-xl"
                    hide-slider
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
                    <v-card variant="outlined" class="settings-card rounded-xl">
                        <form @submit.prevent="submitProfile">
                            <!-- Card Header -->
                            <div class="d-flex align-center px-8 pt-8 pb-4">
                                <v-icon icon="mdi-account-outline" size="28" class="mr-4" color="black"></v-icon>
                                <span class="text-h6 font-weight-medium" style="color: #000;">Profil Informasi Pribadi</span>
                            </div>

                            <!-- Card Content -->
                            <v-card-text class="px-8 pb-4 pt-4">
                                <v-row>
                                    <v-col cols="12" md="6" class="pb-2">
                                        <div class="text-subtitle-2 font-weight-medium mb-2" style="color: #000;">Full Name</div>
                                        <v-text-field
                                            v-model="form.name"
                                            variant="solo-filled"
                                            flat
                                            bg-color="#F3F4F6"
                                            density="comfortable"
                                            hide-details="auto"
                                            rounded="lg"
                                            :error-messages="form.errors.name"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="6" class="pb-2">
                                        <div class="text-subtitle-2 font-weight-medium mb-2" style="color: #000;">Phone Number</div>
                                        <v-text-field
                                            v-model="form.phone"
                                            variant="solo-filled"
                                            flat
                                            bg-color="#F3F4F6"
                                            density="comfortable"
                                            hide-details="auto"
                                            rounded="lg"
                                            :error-messages="form.errors.phone"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="12" md="6" class="pt-2 pb-4">
                                        <div class="text-subtitle-2 font-weight-medium mb-2" style="color: #000;">Email</div>
                                        <v-text-field
                                            v-model="form.email"
                                            variant="solo-filled"
                                            flat
                                            bg-color="#F3F4F6"
                                            density="comfortable"
                                            hide-details="auto"
                                            rounded="lg"
                                            :error-messages="form.errors.email"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="6" class="pt-2 pb-4">
                                        <div class="text-subtitle-2 font-weight-medium mb-2" style="color: #000;">Role</div>
                                        <v-text-field
                                            :model-value="user.role"
                                            variant="solo-filled"
                                            flat
                                            bg-color="#E5E7EB"
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
                            <v-divider class="mx-8 border-opacity-100" color="#E5E7EB"></v-divider>

                            <!-- Card Actions -->
                            <v-card-actions class="px-8 py-6 justify-end">
                                <v-btn
                                    variant="outlined"
                                    color="black"
                                    class="text-none px-6 mr-3 rounded-lg border-grey-lighten-2 font-weight-medium"
                                    height="44"
                                    @click="form.reset()"
                                >
                                    Cancel
                                </v-btn>
                                <v-btn
                                    type="submit"
                                    variant="flat"
                                    color="#C67D53"
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

                <!-- Notifications Tab Content (From the image) -->
                <v-window-item value="notifications">
                    <v-card variant="outlined" class="settings-card rounded-xl">
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
                
                <!-- Placeholders for other tabs -->
                <v-window-item value="security">
                    <v-card variant="outlined" class="settings-card rounded-xl">
                        <form @submit.prevent="updatePassword">
                            <!-- Card Header -->
                            <div class="d-flex align-center px-8 pt-8 pb-4">
                                <v-icon icon="mdi-lock-outline" size="28" class="mr-4" color="black"></v-icon>
                                <span class="text-h6 font-weight-medium" style="color: #000;">Security Settings</span>
                            </div>

                            <!-- Card Content -->
                            <v-card-text class="px-8 pb-4 pt-4">
                                <v-row>
                                    <v-col cols="12" class="pb-2">
                                        <div class="text-subtitle-2 font-weight-medium mb-2" style="color: #000;">Current Password</div>
                                        <v-text-field
                                            v-model="passwordForm.current_password"
                                            type="password"
                                            variant="solo-filled"
                                            flat
                                            bg-color="#F3F4F6"
                                            density="comfortable"
                                            hide-details="auto"
                                            rounded="lg"
                                            :error-messages="passwordForm.errors.current_password"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="12" class="py-2">
                                        <div class="text-subtitle-2 font-weight-medium mb-2" style="color: #000;">New Password</div>
                                        <v-text-field
                                            v-model="passwordForm.password"
                                            type="password"
                                            variant="solo-filled"
                                            flat
                                            bg-color="#F3F4F6"
                                            density="comfortable"
                                            hide-details="auto"
                                            rounded="lg"
                                            :error-messages="passwordForm.errors.password"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="12" class="pt-2 pb-4">
                                        <div class="text-subtitle-2 font-weight-medium mb-2" style="color: #000;">Confirm New Password</div>
                                        <v-text-field
                                            v-model="passwordForm.password_confirmation"
                                            type="password"
                                            variant="solo-filled"
                                            flat
                                            bg-color="#F3F4F6"
                                            density="comfortable"
                                            hide-details="auto"
                                            rounded="lg"
                                            :error-messages="passwordForm.errors.password_confirmation"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
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
                                    @click="passwordForm.reset()"
                                >
                                    Cancel
                                </v-btn>
                                <v-btn
                                    type="submit"
                                    variant="flat"
                                    color="#C67D53"
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
                    <v-card variant="outlined" class="settings-card rounded-xl pa-10 text-center text-grey">
                        <v-icon size="48" class="mb-4">mdi-palette-outline</v-icon>
                        <div class="text-h6">Appearance Settings</div>
                        <div class="text-body-1 mt-2">Customize the look and feel.</div>
                    </v-card>
                </v-window-item>

                <v-window-item value="system">
                    <v-card variant="outlined" class="settings-card rounded-xl">
                        <!-- Card Header -->
                        <div class="d-flex align-center px-8 pt-8 pb-4">
                            <v-icon icon="mdi-database-export-outline" size="28" class="mr-4" color="black"></v-icon>
                            <span class="text-h6 font-weight-medium" style="color: #000;">Database Backup</span>
                        </div>

                        <!-- Card Content -->
                        <v-card-text class="px-8 pb-8 pt-4">
                            <div class="d-flex align-center justify-space-between mb-2">
                                <div>
                                    <div class="text-subtitle-1 font-weight-medium" style="color: #000;">Export Database</div>
                                    <div class="text-body-1 mt-1" style="color: #6B7280;">Download a complete backup of your database as .sql file</div>
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
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Tab background container */
.custom-tabs {
    background-color: #EEF0F3; /* Very light grey */
    padding: 4px;
    display: inline-flex;
}

/* Tab text styling */
.custom-tab {
    font-size: 1rem;
    letter-spacing: 0;
    font-weight: 500;
    color: #000000;
    margin-right: 4px;
}

/* Inactive tab color */
:deep(.v-tab:not(.v-tab--selected)) {
    color: #000000 !important; /* The image shows inactive text as quite dark, maybe pure black but no bg */
}

/* Active tab styling */
:deep(.v-tab--selected) {
    background-color: #FFFFFF !important;
    color: #000000 !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

/* Settings card styling */
.settings-card {
    border-color: #E5E7EB; /* Lighter border color */
    background-color: #FFFFFF;
}
</style>

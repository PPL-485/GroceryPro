<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useTheme } from 'vuetify';

const user = usePage().props.auth.user;
const isAdmin = user.role === 'admin';

const SETTINGS_TAB_KEY = 'grocerypro-settings-tab';
const availableTabs = computed(() => {
    const tabs = ['info_settings', 'notifications', 'security', 'appearance'];
    if (isAdmin) tabs.push('system');
    return tabs;
});

const getInitialTab = () => {
    if (typeof window === 'undefined') return 'info_settings';

    const savedTab = localStorage.getItem(SETTINGS_TAB_KEY);
    return availableTabs.value.includes(savedTab) ? savedTab : 'info_settings';
};

const tab = ref(getInitialTab());

const preferencesForm = useForm({
    receive_low_stock_alerts: user.receive_low_stock_alerts ?? true,
});

const submitPreferences = () => {
    preferencesForm.patch(route('profile.preferences.update'), {
        preserveScroll: true,
        onSuccess: () => {
            snackbar.value = { show: true, text: 'Preferences updated successfully!', color: 'success' };
        },
        onError: () => {
            snackbar.value = { show: true, text: 'Failed to update preferences.', color: 'error' };
        }
    });
};

const snackbar = ref({
    show: false,
    text: '',
    color: 'success'
});

const form = useForm({
    name: user.name,
    phone: user.phone || '',
    email: user.email,
    profile_photo: null,
});

const photoInput = ref(null);
const photoPreview = ref(null);
const selectedPhotoName = ref('');

const handlePhotoUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    form.profile_photo = file;
    selectedPhotoName.value = file.name;
    
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const submitProfile = () => {
    form.post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            selectedPhotoName.value = '';
            snackbar.value = { show: true, text: 'Profile updated successfully!', color: 'success' };
        },
        onError: () => {
            snackbar.value = { show: true, text: 'Failed to update profile.', color: 'error' };
        }
    });
};

const resetProfileForm = () => {
    form.reset();
    form.clearErrors();
    form.profile_photo = null;
    photoPreview.value = null;
    selectedPhotoName.value = '';
    if (photoInput.value) photoInput.value.value = null;
};

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            snackbar.value = { show: true, text: 'Password updated successfully!', color: 'success' };
        },
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset('password', 'password_confirmation');
            }
            if (passwordForm.errors.current_password) {
                passwordForm.reset('current_password');
            }
            snackbar.value = { show: true, text: 'Failed to update password.', color: 'error' };
        },
    });
};

const fileInput = ref(null);
const restoreForm = useForm({
    file: null,
});
const restoreDialog = ref(false);
const selectedRestoreFile = ref(null);

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    selectedRestoreFile.value = file;
    restoreForm.file = file;
    restoreDialog.value = true;
};

const clearRestoreFile = () => {
    selectedRestoreFile.value = null;
    restoreForm.reset();
    if (fileInput.value) fileInput.value.value = null;
};

const cancelRestore = () => {
    restoreDialog.value = false;
    clearRestoreFile();
};

const confirmRestore = () => {
    if (!restoreForm.file) return;

    restoreForm.post(route('restore'), {
        preserveScroll: true,
        onSuccess: () => {
            snackbar.value = { show: true, text: 'Database restored successfully!', color: 'success' };
            restoreDialog.value = false;
            clearRestoreFile();
        },
        onError: () => {
            snackbar.value = { show: true, text: 'Failed to restore database.', color: 'error' };
        }
    });
};

const theme = useTheme();
const themes = ['brand', 'dark'];
const THEME_KEY = 'grocerypro-theme';

const themeIcon = computed(() => {
    if (theme.global.name.value === 'dark') return 'mdi-weather-night';
    if (theme.global.name.value === 'brand') return 'mdi-leaf';
    return 'mdi-theme-light-dark';
});

const themeNameDisplay = computed(() => {
    if (theme.global.name.value === 'dark') return 'Dark Mode';
    if (theme.global.name.value === 'brand') return 'Brand Mode';
    return 'Light Mode';
});

function toggleTheme() {
    const currentIndex = themes.indexOf(theme.global.name.value);
    const nextIndex = (currentIndex + 1) % themes.length;
    theme.global.name.value = themes[nextIndex];
    localStorage.setItem(THEME_KEY, theme.global.name.value);
}

watch(tab, (value) => {
    if (typeof window === 'undefined') return;

    if (availableTabs.value.includes(value)) {
        localStorage.setItem(SETTINGS_TAB_KEY, value);
    }
});

onMounted(() => {
    if (!availableTabs.value.includes(tab.value)) tab.value = 'info_settings';
});
</script>

<template>
    <Head title="store_info" />

    <AuthenticatedLayout>
        <template #header-title>Settings</template>
        <template #header-description>
            <p class="text-sm text-grey-darken-1">
                Manage your account preferences and application settings
            </p>
        </template>
        
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
                    <v-tab value="info_settings" class="text-none custom-tab" rounded="xl" :ripple="false">
                        <v-icon start>mdi-account-outline</v-icon>
                        Info Settings
                    </v-tab>
                    <v-tab value="notifications" class="text-none custom-tab" rounded="xl" :ripple="false">
                        <v-icon start>mdi-bell-outline</v-icon>
                        Notifications
                    </v-tab>
                    <v-tab value="security" class="text-none custom-tab" rounded="xl" :ripple="false">
                        <v-icon start>mdi-lock-outline</v-icon>
                        Security
                    </v-tab>
                    <v-tab value="appearance" class="text-none custom-tab" rounded="xl" :ripple="false">
                        <v-icon start>mdi-palette-outline</v-icon>
                        Appearance
                    </v-tab>
                    <v-tab v-if="isAdmin" value="system" class="text-none custom-tab" rounded="xl" :ripple="false">
                        <v-icon start>mdi-database-outline</v-icon>
                        System
                    </v-tab>
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
                                <div class="d-flex align-center justify-center mb-8">
                                    <div class="position-relative">
                                        <v-avatar size="120" color="grey-lighten-2" class="border">
                                            <v-img v-if="photoPreview" :src="photoPreview" cover></v-img>
                                            <v-img v-else-if="user.profile_photo_path" :src="'/storage/' + user.profile_photo_path" cover></v-img>
                                            <v-icon v-else size="48" color="grey">mdi-account</v-icon>
                                        </v-avatar>
                                        <v-btn
                                            icon="mdi-camera"
                                            size="small"
                                            color="primary"
                                            class="position-absolute"
                                            style="bottom: 0; right: 0; z-index: 10;"
                                            @click="photoInput.click()"
                                        ></v-btn>
                                        <input
                                            type="file"
                                            ref="photoInput"
                                            accept="image/*"
                                            class="d-none"
                                            @change="handlePhotoUpload"
                                        >
                                    </div>
                                </div>
                                <div v-if="selectedPhotoName" class="text-center text-body-2 text-medium-emphasis mb-6">
                                    Selected photo: {{ selectedPhotoName }}
                                </div>
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
                                            @update:model-value="val => form.phone = val ? val.replace(/\D/g, '') : ''"
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
                            <v-card-actions class="settings-actions settings-security-actions px-8 py-6 justify-end">
                                <v-btn
                                    variant="flat"
                                    class="settings-action-button text-none px-6 mr-3 rounded-lg border-grey-lighten-2 font-weight-medium"
                                    height="44"
                                    @click="resetProfileForm"
                                    :disabled="form.processing"
                                >
                                    Cancel
                                </v-btn>
                                <v-btn
                                    type="submit"
                                    variant="flat"
                                    color="primary"
                                    class="settings-action-button text-none px-6 rounded-lg text-white font-weight-medium"
                                    height="44"
                                    :loading="form.processing"
                                    :disabled="form.processing || (!form.isDirty && !photoPreview)"
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
                            <div class="settings-notification-row d-flex align-center justify-space-between mb-2">
                                <div>
                                    <div class="text-subtitle-1 font-weight-medium">Low Stock Alerts</div>
                                    <div class="text-body-1 mt-1">Get notified when products are running low</div>
                                </div>
                                <v-switch
                                    v-model="preferencesForm.receive_low_stock_alerts"
                                    inset
                                    hide-details
                                    color="primary"
                                    density="compact"
                                ></v-switch>
                            </div>
                        </v-card-text>

                        <!-- Divider -->
                        <v-divider class="mx-8" color="#E5E7EB"></v-divider>

                        <!-- Card Actions -->
                        <v-card-actions class="settings-actions settings-notification-actions px-8 py-6 justify-end">
                            <v-btn
                                variant="flat"
                                class="settings-action-button text-none px-6 mr-3 rounded-lg border-grey-lighten-2 font-weight-medium"
                                height="44"
                                @click="preferencesForm.reset()"
                                :disabled="preferencesForm.processing || !preferencesForm.isDirty"
                            >
                                Reset to Default
                            </v-btn>
                            <v-btn
                                variant="flat"
                                color="primary"
                                class="settings-action-button text-none px-6 rounded-lg text-white font-weight-medium"
                                height="44"
                                :loading="preferencesForm.processing"
                                :disabled="preferencesForm.processing || !preferencesForm.isDirty"
                                @click="submitPreferences"
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
                                            :type="showCurrentPassword ? 'text' : 'password'"
                                            variant="outlined"
                                            density="comfortable"
                                            hide-details="auto"
                                            rounded="lg"
                                            :error-messages="passwordForm.errors.current_password"
                                            :append-inner-icon="showCurrentPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                                            @click:append-inner="showCurrentPassword = !showCurrentPassword"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="12" class="py-2">
                                        <div class="text-subtitle-2 font-weight-medium mb-2">New Password</div>
                                        <v-text-field
                                            v-model="passwordForm.password"
                                            :type="showNewPassword ? 'text' : 'password'"
                                            variant="outlined"
                                            density="comfortable"
                                            hide-details="auto"
                                            rounded="lg"
                                            :error-messages="passwordForm.errors.password"
                                            :append-inner-icon="showNewPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                                            @click:append-inner="showNewPassword = !showNewPassword"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="12" class="pt-2 pb-4">
                                        <div class="text-subtitle-2 font-weight-medium mb-2">Confirm New Password</div>
                                        <v-text-field
                                            v-model="passwordForm.password_confirmation"
                                            :type="showConfirmPassword ? 'text' : 'password'"
                                            variant="outlined"
                                            density="comfortable"
                                            hide-details="auto"
                                            rounded="lg"
                                            :error-messages="passwordForm.errors.password_confirmation"
                                            :append-inner-icon="showConfirmPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                                            @click:append-inner="showConfirmPassword = !showConfirmPassword"
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
                                    :disabled="passwordForm.processing || !passwordForm.isDirty"
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
                                    :disabled="passwordForm.processing || !passwordForm.isDirty"
                                >
                                    Update Password
                                </v-btn>
                            </v-card-actions>
                        </form>
                    </v-card>
                </v-window-item>

                <v-window-item value="appearance">
                    <v-card hover flat class="rounded-xl border">
                        <!-- Card Header -->
                        <div class="d-flex align-center px-8 pt-8 pb-4">
                            <v-icon icon="mdi-palette-outline" size="28" class="mr-4" color="primary"></v-icon>
                            <span class="text-h6 font-weight-medium text-primary">Appearance Settings</span>
                        </div>

                        <!-- Card Content -->
                        <v-card-text class="px-8 pb-8 pt-4">
                            <div class="settings-appearance-row d-flex align-center justify-space-between mb-2">
                                <div>
                                    <div class="text-subtitle-1 font-weight-medium">App Theme</div>
                                    <div class="text-body-1 mt-1" style="color: #6B7280;">Change the application's color theme</div>
                                </div>
                                <div class="settings-theme-control d-flex align-center">
                                    <span class="mr-4 text-subtitle-2 text-primary font-weight-bold">{{ themeNameDisplay }}</span>
                                    <v-btn
                                        variant="tonal"
                                        color="primary"
                                        class="rounded-lg text-none font-weight-medium"
                                        height="44"
                                        @click="toggleTheme"
                                    >
                                        <v-icon start>{{ themeIcon }}</v-icon>
                                        Toggle Theme
                                    </v-btn>
                                </div>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-window-item>

                <v-window-item v-if="isAdmin" value="system">
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
                                        :disabled="restoreForm.processing"
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

        <v-dialog v-model="restoreDialog" max-width="520" persistent>
            <v-card class="rounded-xl">
                <v-card-title class="d-flex align-center px-6 pt-6 pb-2">
                    <v-icon icon="mdi-alert-outline" color="warning" class="mr-3"></v-icon>
                    Confirm Database Restore
                </v-card-title>
                <v-card-text class="px-6 pt-2">
                    <div class="text-body-1 mb-3">
                        Restoring a backup will overwrite current database data.
                    </div>
                    <v-alert
                        type="warning"
                        variant="tonal"
                        density="comfortable"
                        class="mb-0"
                    >
                        {{ selectedRestoreFile?.name || 'Selected backup file' }}
                    </v-alert>
                </v-card-text>
                <v-card-actions class="px-6 pb-6 justify-end">
                    <v-btn
                        variant="text"
                        class="text-none"
                        :disabled="restoreForm.processing"
                        @click="cancelRestore"
                    >
                        Cancel
                    </v-btn>
                    <v-btn
                        variant="flat"
                        color="warning"
                        class="text-none text-white"
                        :loading="restoreForm.processing"
                        @click="confirmRestore"
                    >
                        Restore Database
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Global Snackbar for Settings -->
        <v-snackbar
            v-model="snackbar.show"
            :color="snackbar.color"
            timeout="3000"
            location="bottom right"
        >
            {{ snackbar.text }}
            <template v-slot:actions>
                <v-btn
                    color="white"
                    variant="text"
                    @click="snackbar.show = false"
                >
                    Close
                </v-btn>
            </template>
        </v-snackbar>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Settings card styling */
.settings-card {
    background-color: rgb(var(--v-theme-surface));
}

@media (max-width: 959px) {
    .settings-notification-row {
        align-items: flex-start !important;
        gap: 16px;
    }

    .settings-notification-row .text-body-1 {
        font-size: 0.86rem !important;
        line-height: 1.35;
    }

    .settings-actions {
        gap: 12px;
        padding-left: 20px !important;
        padding-right: 20px !important;
    }

    .settings-actions .settings-action-button {
        margin-right: 0 !important;
        min-width: 0;
    }

    .settings-notification-actions {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    }

    .settings-notification-actions .settings-action-button {
        width: 100%;
        padding-inline: 10px !important;
    }

    .settings-security-actions {
        flex-direction: column;
        align-items: stretch !important;
    }

    .settings-security-actions .settings-action-button {
        width: 100%;
    }

    .settings-appearance-row {
        align-items: flex-start !important;
        flex-direction: column;
        gap: 14px;
    }

    .settings-theme-control {
        align-items: stretch !important;
        flex-direction: column;
        gap: 10px;
        width: 100%;
    }

    .settings-theme-control .text-subtitle-2 {
        margin-right: 0 !important;
    }

    .settings-theme-control .v-btn {
        width: 100%;
    }
}
</style>

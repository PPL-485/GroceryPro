<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const tab = ref('notifications');
const lowStockAlerts = ref(true);
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
                    <v-tab value="store_info" class="text-none custom-tab" rounded="xl" :ripple="false">Store Info</v-tab>
                    <v-tab value="notifications" class="text-none custom-tab" rounded="xl" :ripple="false">Notifications</v-tab>
                    <v-tab value="security" class="text-none custom-tab" rounded="xl" :ripple="false">Security</v-tab>
                    <v-tab value="appearance" class="text-none custom-tab" rounded="xl" :ripple="false">Appearance</v-tab>
                    <v-tab value="system" class="text-none custom-tab" rounded="xl" :ripple="false">System</v-tab>
                </v-tabs>
            </div>

            <!-- Tab Windows -->
            <v-window v-model="tab" class="overflow-visible px-2">
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
                <v-window-item value="store_info">
                    <v-card variant="outlined" class="settings-card rounded-xl pa-10 text-center text-grey">
                        <v-icon size="48" class="mb-4">mdi-store</v-icon>
                        <div class="text-h6">Store Info Settings</div>
                        <div class="text-body-1 mt-2">Configuration for store information.</div>
                    </v-card>
                </v-window-item>

                <v-window-item value="security">
                    <v-card variant="outlined" class="settings-card rounded-xl pa-10 text-center text-grey">
                        <v-icon size="48" class="mb-4">mdi-shield-check-outline</v-icon>
                        <div class="text-h6">Security Settings</div>
                        <div class="text-body-1 mt-2">Manage security and passwords.</div>
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

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const tab = ref('store info');
const lowStockAlerts = ref(true);
</script>

<template>
    <Head title="store_info" />

    <AuthenticatedLayout>
        <!-- Provide title/description slots to the layout's AppBar if desired, 
             but we will render a prominent header inside the page content to match the design. -->
        <template #header-title>Settings</template>
        <template #header-description>
            <p class="text-sm">
                Configure your store settings and preferences
            </p>
        </template>
        <!-- Tabs -->
        <div class="px-2">
            <v-tabs
                v-model="tab"
                color="primary"
                align-tabs="start"
                class="mb-6"
                density="compact"
            >
                <v-tab value="store_info" :ripple="false">Store Info</v-tab>
                <v-tab value="notifications" :ripple="false">Notifications</v-tab>
                <v-tab value="security" :ripple="false">Security</v-tab>
                <v-tab value="appearance" :ripple="false">Appearance</v-tab>
                <v-tab value="system" :ripple="false">System</v-tab>
            </v-tabs>
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

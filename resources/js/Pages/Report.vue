<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    transactions: Array,
    stats: Object,
});

const user = usePage().props.auth.user;
const isAdmin = user.role === 'admin';

const tab = ref('transaction_history');

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
};

const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

const getPaymentColor = (type) => {
    if (type?.toLowerCase() === 'cash') return 'success';
    if (type?.toLowerCase() === 'qris') return 'success';
    return 'default';
};
</script>

<template>
    <Head title="Reports & Analytics" />

    <AuthenticatedLayout>
        <template #header-title>
            Reports & Analytics
        </template>
        
        <template #header-description>
            View detailed reports and insights about your business
        </template>
        
        <div class="px-2 pb-6 max-w-7xl mx-auto mt-4">

            <!-- Summary Cards -->
            <v-row class="mb-6">
                <!-- Total Revenue -->
                <v-col cols="12" sm="6" md="3">
                    <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                        <v-card-text>
                            <div class="d-flex justify-space-between align-start">
                                <div>
                                    <div class="text-caption text-grey-darken-1 font-weight-medium mb-1">Total Revenue</div>
                                    <div class="text-h5 font-weight-bold">
                                        {{ formatCurrency(stats.totalRevenue) }}
                                    </div>
                                    <div class="text-caption mt-2 text-success font-weight-medium d-flex align-center">
                                        <v-icon size="small" class="mr-1">mdi-trending-up</v-icon>
                                        +12.5% <span class="text-grey ml-1 font-weight-regular">vs last period</span>
                                    </div>
                                </div>
                                <v-avatar color="primary" size="40" rounded>
                                    <v-icon color="white">mdi-currency-usd</v-icon>
                                </v-avatar>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>

                <!-- Total Profit -->
                <v-col cols="12" sm="6" md="3">
                    <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                        <v-card-text>
                            <div class="d-flex justify-space-between align-start">
                                <div>
                                    <div class="text-caption text-grey-darken-1 font-weight-medium mb-1">Total Profit</div>
                                    <div class="text-h5 font-weight-bold">
                                        {{ formatCurrency(stats.totalProfit) }}
                                    </div>
                                    <div class="text-caption mt-2 text-success font-weight-medium d-flex align-center">
                                        <v-icon size="small" class="mr-1">mdi-trending-up</v-icon>
                                        +8.3% <span class="text-grey ml-1 font-weight-regular">vs last period</span>
                                    </div>
                                </div>
                                <v-avatar color="primary" size="40" rounded>
                                    <v-icon color="white">mdi-trending-up</v-icon>
                                </v-avatar>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>

                <!-- Transactions -->
                <v-col cols="12" sm="6" md="3">
                    <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                        <v-card-text>
                            <div class="d-flex justify-space-between align-start">
                                <div>
                                    <div class="text-caption text-grey-darken-1 font-weight-medium mb-1">Transactions</div>
                                    <div class="text-h5 font-weight-bold">
                                        {{ formatNumber(stats.transactionsCount) }}
                                    </div>
                                    <div class="text-caption mt-2 text-success font-weight-medium d-flex align-center">
                                        <v-icon size="small" class="mr-1">mdi-trending-up</v-icon>
                                        +5.2% <span class="text-grey ml-1 font-weight-regular">vs last period</span>
                                    </div>
                                </div>
                                <v-avatar color="primary" size="40" rounded>
                                    <v-icon color="white">mdi-cart-outline</v-icon>
                                </v-avatar>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>

                <!-- Avg Transaction -->
                <v-col cols="12" sm="6" md="3">
                    <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                        <v-card-text>
                            <div class="d-flex justify-space-between align-start">
                                <div>
                                    <div class="text-caption text-grey-darken-1 font-weight-medium mb-1">Avg Transaction</div>
                                    <div class="text-h5 font-weight-bold">
                                        {{ formatCurrency(stats.avgTransaction) }}
                                    </div>
                                    <div class="text-caption mt-2 text-success font-weight-medium d-flex align-center">
                                        <v-icon size="small" class="mr-1">mdi-trending-up</v-icon>
                                        +3.1% <span class="text-grey ml-1 font-weight-regular">vs last period</span>
                                    </div>
                                </div>
                                <v-avatar color="primary" size="40" rounded>
                                    <v-icon color="white">mdi-package-variant</v-icon>
                                </v-avatar>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>

            <!-- Tabs & Actions -->
            <div class="px-2 d-flex justify-space-between align-center mb-6">
                <v-tabs
                    v-model="tab"
                    color="primary"
                    align-tabs="start"
                    class="rounded-xl flex-grow-1"
                    height="48"
                >
                    <v-tab v-if="isAdmin" value="sales_report" class="text-none font-weight-medium" rounded="xl" :ripple="false">Sales Report</v-tab>
                    <v-tab v-if="isAdmin" value="product_performance" class="text-none font-weight-medium" rounded="xl" :ripple="false">Product Performance</v-tab>
                    <v-tab v-if="isAdmin" value="inventory_report" class="text-none font-weight-medium" rounded="xl" :ripple="false">Inventory Report</v-tab>
                    <v-tab value="transaction_history" class="text-none font-weight-medium" rounded="xl" :ripple="false">Transaction History</v-tab>
                </v-tabs>
                
                <div class="d-flex align-center ga-3 pl-4 flex-grow-0">
                    <v-select
                        :items="['Daily', 'Weekly', 'Monthly']"
                        model-value="Daily"
                        variant="outlined"
                        density="compact"
                        hide-details
                        bg-color="surface"
                        class="rounded-lg"
                        style="width: 130px;"
                    ></v-select>
                    <v-btn
                        variant="outlined"
                        color="primary"
                        prepend-icon="mdi-calendar-range"
                        class="text-none font-weight-medium bg-surface border-grey-lighten-2 rounded-lg"
                        height="40"
                    >
                        Date Range
                    </v-btn>
                    <v-btn
                        variant="flat"
                        color="primary"
                        prepend-icon="mdi-export"
                        class="text-none font-weight-medium text-white rounded-lg"
                        height="40"
                    >
                        Export Report
                    </v-btn>
                </div>
            </div>

            <!-- Tab Content -->
            <v-window v-model="tab" class="overflow-visible px-2">
                
                <!-- Placeholders for other tabs -->
                <v-window-item v-if="isAdmin" value="sales_report">
                    <v-card hover flat class="rounded-xl border pa-10 text-center text-grey">
                        <v-icon size="48" class="mb-4" color="primary">mdi-chart-line</v-icon>
                        <div class="text-h6 text-primary">Sales Report Placeholder</div>
                        <div class="text-body-1 mt-2" style="color: #6B7280;">Sales detailed statistics will go here.</div>
                    </v-card>
                </v-window-item>
                
                <v-window-item v-if="isAdmin" value="product_performance">
                    <v-card hover flat class="rounded-xl border pa-10 text-center text-grey">
                        <v-icon size="48" class="mb-4" color="primary">mdi-package-variant-closed</v-icon>
                        <div class="text-h6 text-primary">Product Performance Placeholder</div>
                        <div class="text-body-1 mt-2" style="color: #6B7280;">Product performance stats will go here.</div>
                    </v-card>
                </v-window-item>
                
                <v-window-item v-if="isAdmin" value="inventory_report">
                    <v-card hover flat class="rounded-xl border pa-10 text-center text-grey">
                        <v-icon size="48" class="mb-4" color="primary">mdi-clipboard-text-outline</v-icon>
                        <div class="text-h6 text-primary">Inventory Report Placeholder</div>
                        <div class="text-body-1 mt-2" style="color: #6B7280;">Inventory detailed reports will go here.</div>
                    </v-card>
                </v-window-item>
                
                <!-- Transaction History Tab -->
                <v-window-item value="transaction_history">
                    <v-card flat class="rounded-xl border mb-6 bg-surface px-6 py-6" v-for="trx in transactions" :key="trx.id">
                        
                        <!-- Header of Transaction Card -->
                        <div class="d-flex justify-space-between mb-4">
                            <div class="d-flex align-center">
                                <v-avatar color="green-lighten-4" size="36" rounded="lg" class="mr-4">
                                    <v-icon color="primary" size="small">mdi-cash-register</v-icon>
                                </v-avatar>
                                <div>
                                    <div class="text-subtitle-1 font-weight-bold mb-1">{{ trx.id }}</div>
                                    <div class="d-flex align-center text-caption text-grey-darken-1">
                                        <v-icon size="x-small" class="mr-1">mdi-calendar</v-icon>
                                        {{ trx.date }}
                                        <span class="mx-2">&bull;</span>
                                        <v-icon size="x-small" class="mr-1">mdi-account</v-icon>
                                        Cashier: {{ trx.cashier }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-caption text-grey-darken-1 mb-1">Total Amount</div>
                                <div class="text-subtitle-1 font-weight-bold mb-1">{{ formatCurrency(trx.total) }}</div>
                                <v-chip size="x-small" :color="getPaymentColor(trx.payment_method)" class="font-weight-medium">
                                    {{ trx.payment_method }}
                                </v-chip>
                            </div>
                        </div>

                        <v-divider class="mb-4"></v-divider>

                        <!-- Items Purchased -->
                        <div class="text-subtitle-2 font-weight-bold mb-3">Items Purchased:</div>
                        
                        <v-table density="compact" class="bg-transparent">
                            <thead>
                                <tr>
                                    <th class="text-left font-weight-medium text-grey-darken-2 pl-0">Product</th>
                                    <th class="text-left font-weight-medium text-grey-darken-2">Qty</th>
                                    <th class="text-right font-weight-medium text-grey-darken-2">Price</th>
                                    <th class="text-right font-weight-medium text-grey-darken-2 pr-0">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, idx) in trx.items" :key="idx">
                                    <td class="pl-0">{{ item.name }}</td>
                                    <td class="">{{ item.qty }}x</td>
                                    <td class=" text-right">{{ formatCurrency(item.price) }}</td>
                                    <td class="text-right pr-0">{{ formatCurrency(item.subtotal) }}</td>
                                </tr>
                            </tbody>
                        </v-table>
                    </v-card>
                </v-window-item>
            </v-window>
        </div>
    </AuthenticatedLayout>
</template>

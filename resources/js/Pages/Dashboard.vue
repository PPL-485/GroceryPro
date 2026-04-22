<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recentTransactions: Array,
    lowStockAlerts: Array,
});

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

// Dummy data for pie chart
const categoryColors = {
    'Instant Noodles': '#4A6925', // dark green
    'Beverages': '#689F38', // lighter green
    'Rice': '#2E4A14', // very dark green
    'Cooking Oil': '#C06B4D', // burnt orange
    'Snacks': '#33501E',
    'Dairy': '#55802E'
};

const getPaymentColor = (type) => {
    if (type?.toLowerCase() === 'cash') return 'success';
    if (type?.toLowerCase() === 'qris') return 'warning';
    return 'default';
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header-title>
            Dashboard
        </template>
        
        <template #header-description>
            <p class="text-sm text-gray-500">
                Welcome back! Here's what's happening with your store today.
            </p>
        </template>
        
        <div class="py-2">
            <!-- Top Stats Row -->
            <v-row class="mb-6">
                <!-- Total Revenue -->
                <v-col cols="12" sm="6" md="3">
                    <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                        <v-card-text>
                            <div class="d-flex justify-space-between align-start">
                                <div>
                                    <div class="text-caption text-grey-darken-1 font-weight-medium mb-1">Total Revenue</div>
                                    <div class="text-h5 font-weight-bold text-grey-darken-4">
                                        {{ formatCurrency(stats.totalRevenue || 0) }}
                                    </div>
                                    <div class="text-caption mt-2 text-success font-weight-medium d-flex align-center">
                                        <v-icon size="small" class="mr-1">mdi-trending-up</v-icon>
                                        +20.1% <span class="text-grey ml-1 font-weight-regular">from last month</span>
                                    </div>
                                </div>
                                <v-avatar color="green-lighten-4" size="40" rounded>
                                    <v-icon color="green-darken-3">mdi-currency-usd</v-icon>
                                </v-avatar>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>

                <!-- Total Products -->
                <v-col cols="12" sm="6" md="3">
                    <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                        <v-card-text>
                            <div class="d-flex justify-space-between align-start">
                                <div>
                                    <div class="text-caption text-grey-darken-1 font-weight-medium mb-1">Total Products</div>
                                    <div class="text-h5 font-weight-bold text-grey-darken-4">
                                        {{ formatNumber(stats.totalProducts || 0) }}
                                    </div>
                                    <div class="text-caption mt-2 text-success font-weight-medium d-flex align-center">
                                        <v-icon size="small" class="mr-1">mdi-trending-up</v-icon>
                                        +180 <span class="text-grey ml-1 font-weight-regular">from last month</span>
                                    </div>
                                </div>
                                <v-avatar color="green-lighten-4" size="40" rounded>
                                    <v-icon color="green-darken-3">mdi-package-variant-closed</v-icon>
                                </v-avatar>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>

                <!-- Transactions Today -->
                <v-col cols="12" sm="6" md="3">
                    <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                        <v-card-text>
                            <div class="d-flex justify-space-between align-start">
                                <div>
                                    <div class="text-caption text-grey-darken-1 font-weight-medium mb-1">Transactions Today</div>
                                    <div class="text-h5 font-weight-bold text-grey-darken-4">
                                        {{ formatNumber(stats.transactionsToday || 0) }}
                                    </div>
                                    <div class="text-caption mt-2 text-success font-weight-medium d-flex align-center">
                                        <v-icon size="small" class="mr-1">mdi-trending-up</v-icon>
                                        +12.5% <span class="text-grey ml-1 font-weight-regular">from last month</span>
                                    </div>
                                </div>
                                <v-avatar color="grey-lighten-3" size="40" rounded>
                                    <v-icon color="grey-darken-3">mdi-cart-outline</v-icon>
                                </v-avatar>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>

                <!-- Low Stock Items -->
                <v-col cols="12" sm="6" md="3">
                    <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                        <v-card-text>
                            <div class="d-flex justify-space-between align-start">
                                <div>
                                    <div class="text-caption text-grey-darken-1 font-weight-medium mb-1">Low Stock Items</div>
                                    <div class="text-h5 font-weight-bold text-grey-darken-4">
                                        {{ formatNumber(stats.lowStockItemsCount || 0) }}
                                    </div>
                                    <div class="text-caption mt-2 text-error font-weight-medium d-flex align-center">
                                        <v-icon size="small" class="mr-1">mdi-trending-down</v-icon>
                                        -3 <span class="text-grey ml-1 font-weight-regular">from last month</span>
                                    </div>
                                </div>
                                <v-avatar color="red-lighten-5" size="40" rounded>
                                    <v-icon color="error">mdi-alert-circle-outline</v-icon>
                                </v-avatar>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>

            <v-row class="mb-6">
                <!-- Recent Transactions -->
                <v-col cols="12" md="8">
                    <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                        <v-card-title class="pt-4 px-4 text-subtitle-2 font-weight-medium text-grey-darken-2">
                            Recent Transactions
                        </v-card-title>
                        <v-card-text class="px-0">
                            <v-table density="comfortable" class="bg-transparent">
                                <thead>
                                    <tr>
                                        <th class="text-left font-weight-medium text-caption text-grey-darken-3 border-bottom">ID</th>
                                        <th class="text-left font-weight-medium text-caption text-grey-darken-3 border-bottom">Customer</th>
                                        <th class="text-left font-weight-medium text-caption text-grey-darken-3 border-bottom">Items</th>
                                        <th class="text-left font-weight-medium text-caption text-grey-darken-3 border-bottom">Total</th>
                                        <th class="text-left font-weight-medium text-caption text-grey-darken-3 border-bottom">Payment Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="trx in recentTransactions" :key="trx.id">
                                        <td class="font-weight-medium text-body-2">{{ trx.id }}</td>
                                        <td class="text-grey-darken-1 text-body-2">{{ trx.customer || 'Customer' }}</td>
                                        <td class="text-grey-darken-1 text-body-2">{{ trx.items || 0 }}</td>
                                        <td class="font-weight-medium text-body-2">{{ formatCurrency(trx.total) }}</td>
                                        <td>
                                            <v-chip
                                                :color="getPaymentColor(trx.payment_type)"
                                                size="small"
                                                variant="flat"
                                                class="font-weight-medium"
                                            >
                                                {{ trx.payment_type }}
                                            </v-chip>
                                        </td>
                                    </tr>
                                    <tr v-if="!recentTransactions || recentTransactions.length === 0">
                                        <td colspan="5" class="text-center text-grey py-4">No recent transactions</td>
                                    </tr>
                                </tbody>
                            </v-table>
                        </v-card-text>
                    </v-card>
                </v-col>

                <!-- Category Performance -->
                <v-col cols="12" md="4">
                    <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                        <v-card-title class="pt-4 px-4 text-subtitle-2 font-weight-medium text-grey-darken-2">
                            Category Performance
                        </v-card-title>
                        <v-card-text class="d-flex flex-column align-center justify-center pt-8">
                            <!-- CSS Pie Chart -->
                            <div class="pie-chart mb-6"></div>
                            
                            <!-- Legend -->
                            <div class="d-flex flex-wrap justify-center gap-2 mt-4" style="gap: 8px 16px;">
                                <div v-for="(color, name) in categoryColors" :key="name" class="d-flex align-center">
                                    <div class="color-box mr-2" :style="{ backgroundColor: color }"></div>
                                    <span class="text-caption text-grey-darken-2">{{ name }}</span>
                                </div>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>

            <!-- Low Stock Alerts -->
            <v-card class="rounded-lg elevation-1 mb-4" variant="flat">
                <v-card-title class="pt-4 px-4 pb-0 text-subtitle-2 font-weight-medium text-grey-darken-2 d-flex align-center">
                    <v-icon color="error" size="small" class="mr-2">mdi-alert-circle-outline</v-icon>
                    Low Stock Alert
                </v-card-title>
                
                <v-card-text class="pt-4">
                    <v-row v-if="lowStockAlerts && lowStockAlerts.length > 0">
                        <v-col cols="12" sm="6" md="3" v-for="item in lowStockAlerts" :key="item.id">
                            <v-card variant="outlined" :color="item.stock_qty === 0 ? 'error' : 'orange-lighten-4'" class="bg-orange-lighten-5 border-opacity-50">
                                <v-card-text class="d-flex justify-space-between">
                                    <div>
                                        <div class="font-weight-medium text-grey-darken-4 text-body-1">{{ item.name }}</div>
                                        <div class="text-caption text-grey-darken-1">{{ item.category?.name || 'Category' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-weight-bold text-deep-orange-darken-2 text-body-1">{{ item.stock_qty }} units</div>
                                        <div class="text-caption text-grey">Min: {{ item.min_stock }} units</div>
                                    </div>
                                </v-card-text>
                            </v-card>
                        </v-col>
                    </v-row>
                    <div v-else class="text-grey text-center py-4">
                        All stocks are above minimum level.
                    </div>
                </v-card-text>
            </v-card>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.pie-chart {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    /* Approximation of the pie chart in the design */
    background: conic-gradient(
        #4A6925 0% 35%,
        #689F38 35% 55%,
        #2E4A14 55% 75%,
        #55802E 75% 85%,
        #33501E 85% 90%,
        #C06B4D 90% 100%
    );
    border: 1px solid rgba(255,255,255,0.8);
    box-shadow: inset 0 0 0 1px rgba(255,255,255,0.5);
    position: relative;
}

/* Adding white lines between segments for the pie chart */
.pie-chart::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    border-radius: 50%;
    /* We can use multiple linear gradients to create lines, or SVG. Let's keep it simple. */
}

.color-box {
    width: 12px;
    height: 12px;
    border-radius: 2px;
}

.border-bottom {
    border-bottom: 1px solid #eeeeee !important;
}

.gap-2 {
    gap: 8px;
}
</style>

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
                                    <div class="text-h5 font-weight-bold">
                                        {{ formatCurrency(stats.totalRevenue || 0) }}
                                    </div>
                                    <div :class="['text-caption mt-2 font-weight-medium d-flex align-center', stats.revenueTrend >= 0 ? 'text-success' : 'text-error']">
                                        <v-icon size="small" class="mr-1">{{ stats.revenueTrend >= 0 ? 'mdi-trending-up' : 'mdi-trending-down' }}</v-icon>
                                        {{ stats.revenueTrend >= 0 ? '+' : '' }}{{ stats.revenueTrend }}% <span class="text-grey ml-1 font-weight-regular">from last month</span>
                                    </div>
                                </div>
                                <v-avatar color="primary" size="40" rounded>
                                    <v-icon color="white">mdi-currency-usd</v-icon>
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
                                    <div class="text-h5 font-weight-bold">
                                        {{ formatNumber(stats.totalProducts || 0) }}
                                    </div>
                                    <div class="text-caption mt-2 text-success font-weight-medium d-flex align-center">
                                        <v-icon size="small" class="mr-1">mdi-plus</v-icon>
                                        +{{ stats.productsTrend || 0 }} <span class="text-grey ml-1 font-weight-regular">added this month</span>
                                    </div>
                                </div>
                                <v-avatar color="primary" size="40" rounded>
                                    <v-icon color="white">mdi-package-variant-closed</v-icon>
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
                                    <div class="text-h5 font-weight-bold">
                                        {{ formatNumber(stats.transactionsToday || 0) }}
                                    </div>
                                    <div :class="['text-caption mt-2 font-weight-medium d-flex align-center', stats.transactionsTrend >= 0 ? 'text-success' : 'text-error']">
                                        <v-icon size="small" class="mr-1">{{ stats.transactionsTrend >= 0 ? 'mdi-trending-up' : 'mdi-trending-down' }}</v-icon>
                                        {{ stats.transactionsTrend >= 0 ? '+' : '' }}{{ stats.transactionsTrend }}% <span class="text-grey ml-1 font-weight-regular">vs yesterday</span>
                                    </div>
                                </div>
                                <v-avatar color="primary" size="40" rounded>
                                    <v-icon color="white">mdi-cart-outline</v-icon>
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
                                    <div class="text-h5 font-weight-bold">
                                        {{ formatNumber(stats.lowStockItemsCount || 0) }}
                                    </div>
                                    <div class="text-caption mt-2 text-grey font-weight-medium d-flex align-center">
                                        <v-icon size="small" class="mr-1">mdi-information-outline</v-icon>
                                        Requires restock
                                    </div>
                                </div>
                                <v-avatar color="error" size="40" rounded>
                                    <v-icon color="white">mdi-alert-circle-outline</v-icon>
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
                            Transactions Today
                        </v-card-title>
                        <v-card-text class="px-0">
                            <v-table hover density="comfortable" class="bg-transparent">
                                <thead>
                                    <tr>
                                        <th class="text-left font-weight-bold text-grey-darken-2">ID</th>
                                        <th class="text-left font-weight-bold text-grey-darken-2">Customer</th>
                                        <th class="text-left font-weight-bold text-grey-darken-2">Items</th>
                                        <th class="text-left font-weight-bold text-grey-darken-2">Total</th>
                                        <th class="text-left font-weight-bold text-grey-darken-2">Payment Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="trx in recentTransactions" :key="trx.id">
                                        <td class="text-grey-darken-1 font-weight-medium text-body-2">{{ trx.id }}</td>
                                        <td class="text-grey-darken-1 text-body-2">{{ trx.customer || 'Customer' }}</td>
                                        <td class="text-grey-darken-1 text-body-2">{{ trx.items || 0 }}</td>
                                        <td class="text-grey-darken-1 font-weight-medium text-body-2">{{ formatCurrency(trx.total) }}</td>
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
                            <div class="d-flex flex-wrap justify-center ga-2 mt-4" style="gap: 8px 16px;">
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
            <v-card class="rounded-lg elevation-1 mb-4" color="surface" variant="flat">
                <v-card-title class="pt-4 px-4 pb-0 text-subtitle-2 font-weight-medium d-flex align-center">
                    <v-icon color="error" size="small" class="mr-2">mdi-alert-circle-outline</v-icon>
                    Low Stock Alert
                </v-card-title>
                
                <v-card-text class="pt-4">
                    <v-row v-if="lowStockAlerts && lowStockAlerts.length > 0">
                        <v-col cols="12" sm="6" md="3" v-for="item in lowStockAlerts" :key="item.id">
                            <v-card hover flat  :color="item.stock_qty === 0 ? 'error' : 'surface'">
                                <v-card-text class="d-flex justify-space-between rounded-xl border">
                                    <div>
                                        <div :class="['font-weight-medium text-body-1', item.stock_qty === 0 ? 'text-white' : 'text-primary']">{{ item.name }}</div>
                                        <div :class="['text-caption', item.stock_qty === 0 ? 'text-white' : 'text-grey']">{{ item.category?.name || 'Category' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div :class="['font-weight-bold text-body-1', item.stock_qty === 0 ? 'text-white' : 'text-deep-orange-darken-2']">{{ item.stock_qty }} units</div>
                                        <div :class="['text-caption', item.stock_qty === 0 ? 'text-white' : 'text-grey']">Min: {{ item.min_stock }} units</div>
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

</style>

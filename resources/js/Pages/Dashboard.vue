<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    recentTransactions: Array,
    lowStockAlerts: Array,
    categoryPerformance: Array,
});

const page = usePage();
const userRole = computed(() => page.props.auth.user.role);
const isAdmin = computed(() => userRole.value === 'admin');

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

// Dynamic data for pie chart
const baseColors = [
    '#4A6925', '#689F38', '#2E4A14', '#C06B4D', 
    '#33501E', '#55802E', '#8E6C3A', '#A0522D',
];

const pieItems = computed(() => {
    if (!props.categoryPerformance) return [];
    
    const total = props.categoryPerformance.reduce((acc, curr) => acc + parseInt(curr.total_sold), 0);
    let currentPercent = 0;
    
    return props.categoryPerformance.map((cat, index) => {
        const percent = total > 0 ? (parseInt(cat.total_sold) / total) * 100 : 0;
        const color = baseColors[index % baseColors.length];
        const start = currentPercent;
        currentPercent += percent;
        
        return {
            title: cat.name,
            value: parseInt(cat.total_sold),
            percent: percent,
            color: color,
            start: start
        };
    });
});

const getPaymentColor = (type) => {
    if (type?.toLowerCase() === 'cash') return 'success';
    if (type?.toLowerCase() === 'qris') return 'warning';
    return 'default';
};

const getStatusColor = (status) => {
    if (status?.toLowerCase() === 'paid') return 'success';
    if (status?.toLowerCase() === 'pending') return 'warning';
    if (status?.toLowerCase() === 'failed') return 'error';
    return 'default';
};

const formatStatus = (status) => {
    if (!status) return 'Completed';
    return status.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
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
                <v-col cols="12" sm="6" md="3" v-if="isAdmin">
                    <Link :href="route('report')" class="dashboard-card-link">
                    <v-card class="rounded-lg elevation-1 h-100 dashboard-clickable-card" variant="flat">
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
                    </Link>
                </v-col>

                <!-- Total Products -->
                <v-col cols="12" sm="6" :md="isAdmin ? 3 : 4">
                    <Link v-if="isAdmin" :href="route('products')" class="dashboard-card-link">
                    <v-card class="rounded-lg elevation-1 h-100 dashboard-clickable-card" variant="flat">
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
                    </Link>
                    <v-card v-else class="rounded-lg elevation-1 h-100" variant="flat">
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
                <v-col cols="12" sm="6" :md="isAdmin ? 3 : 4">
                    <Link :href="route('transactions')" class="dashboard-card-link">
                    <v-card class="rounded-lg elevation-1 h-100 dashboard-clickable-card" variant="flat">
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
                    </Link>
                </v-col>

                <!-- Low Stock Items -->
                <v-col cols="12" sm="6" :md="isAdmin ? 3 : 4">
                    <Link v-if="isAdmin" :href="route('products')" class="dashboard-card-link">
                    <v-card class="rounded-lg elevation-1 h-100 dashboard-clickable-card" variant="flat">
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
                    </Link>
                    <v-card v-else class="rounded-lg elevation-1 h-100" variant="flat">
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
                <v-col cols="12" :md="isAdmin ? 8 : 12">
                    <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                        <v-card-title class="pt-4 px-4 text-subtitle-2 font-weight-medium text-grey-darken-2 d-flex align-center">
                            <span>Transactions Today</span>
                            <v-spacer></v-spacer>
                            <Link :href="route('report')" class="text-decoration-none">
                                <v-btn size="small" variant="text" color="primary" class="text-none">
                                    View report
                                    <v-icon end size="small">mdi-arrow-right</v-icon>
                                </v-btn>
                            </Link>
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
                                        <th class="text-left font-weight-bold text-grey-darken-2">Status</th>
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
                                        <td>
                                            <v-chip
                                                :color="getStatusColor(trx.status)"
                                                size="small"
                                                variant="tonal"
                                                class="font-weight-medium"
                                            >
                                                {{ formatStatus(trx.status) }}
                                            </v-chip>
                                        </td>
                                    </tr>
                                    <tr v-if="!recentTransactions || recentTransactions.length === 0">
                                        <td colspan="6" class="text-center text-grey py-4">No recent transactions</td>
                                    </tr>
                                </tbody>
                            </v-table>
                        </v-card-text>
                    </v-card>
                </v-col>

                <!-- Category Performance -->
                <v-col cols="12" md="4" v-if="isAdmin">
                    <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                        <v-card-title class="pt-4 px-4 text-subtitle-2 font-weight-medium text-grey-darken-2">
                            Category Performance
                        </v-card-title>
                        <v-card-text class="d-flex flex-column align-center justify-center pt-8 pb-4">
                            <!-- Vuetify Progress Circular Donut Chart -->
                            <div class="position-relative d-flex justify-center mx-auto mb-6" style="width: 240px; height: 240px;">
                                <template v-if="pieItems.length > 0 && pieItems.some(i => i.value > 0)">
                                    <v-progress-circular
                                        v-for="item in pieItems"
                                        :key="item.title"
                                        :model-value="item.percent"
                                        :color="item.color"
                                        :size="240"
                                        :width="45"
                                        :rotate="(item.start / 100) * 360 - 90"
                                        bg-color="transparent"
                                        class="position-absolute"
                                        style="top: 0; left: 0; transition: all 0.5s ease; cursor: pointer;"
                                    >
                                        <v-tooltip activator="parent" location="top">
                                            {{ item.title }}: {{ formatNumber(item.value) }} units ({{ Math.round(item.percent) }}%)
                                        </v-tooltip>
                                    </v-progress-circular>
                                </template>
                                <v-progress-circular
                                    v-else
                                    :model-value="100"
                                    color="grey-lighten-2"
                                    :size="240"
                                    :width="45"
                                ></v-progress-circular>
                            </div>

                            <!-- Legend -->
                            <div class="w-100 mt-2 px-4" v-if="pieItems.length > 0 && pieItems.some(i => i.value > 0)">
                                <div v-for="item in pieItems" :key="item.title" class="d-flex align-center mb-3">
                                    <div class="color-box mr-3" :style="{ backgroundColor: item.color, borderRadius: '50%' }"></div>
                                    <div class="d-flex w-100 text-body-2 text-grey-darken-2">
                                        <div>{{ item.title }}</div>
                                        <div class="ml-auto font-weight-bold">
                                            {{ formatNumber(item.value) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-caption text-grey py-8 text-center">
                                No sales data available
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>

            <!-- Low Stock Alerts -->
            <v-card class="rounded-lg elevation-1 mb-4" color="surface" variant="flat">
                <v-card-title class="pt-4 px-4 pb-0 text-subtitle-2 font-weight-medium d-flex align-center">
                    <div class="d-flex align-center">
                        <v-icon color="error" size="small" class="mr-2">mdi-alert-circle-outline</v-icon>
                        Low Stock Alert
                    </div>
                    <v-spacer></v-spacer>
                    <Link v-if="isAdmin" :href="route('products')" class="text-decoration-none">
                        <v-btn size="small" variant="text" color="primary" class="text-none">
                            Manage stock
                            <v-icon end size="small">mdi-arrow-right</v-icon>
                        </v-btn>
                    </Link>
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
.color-box {
    width: 12px;
    height: 12px;
    border-radius: 2px;
}

.dashboard-card-link {
    display: block;
    height: 100%;
    color: inherit;
    text-decoration: none;
}

.dashboard-clickable-card {
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.dashboard-clickable-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08) !important;
}
</style>

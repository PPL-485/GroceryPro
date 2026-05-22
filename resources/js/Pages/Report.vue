<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    transactions: Array,
    stats: Object,
    inventoryMovements: Array,
    dailySales: Array,
    productPerformance: Array,
    filters: Object,
});

const user = usePage().props.auth.user;
const isAdmin = user.role === 'admin';

// Product Performance state & computation
const productSearch = ref('');
const productSortBy = ref('units_sold');
const productSortDesc = ref(true);

const filteredProductPerformance = computed(() => {
    let result = props.productPerformance || [];
    
    if (productSearch.value) {
        const query = productSearch.value.toLowerCase();
        result = result.filter(item => 
            item.product_name?.toLowerCase().includes(query) || 
            item.sku?.toLowerCase().includes(query) ||
            item.category_name?.toLowerCase().includes(query)
        );
    }
    
    // Sort
    result = [...result].sort((a, b) => {
        let fieldA = a[productSortBy.value];
        let fieldB = b[productSortBy.value];
        
        if (typeof fieldA === 'string') {
            return productSortDesc.value 
                ? fieldB.localeCompare(fieldA) 
                : fieldA.localeCompare(fieldB);
        } else {
            return productSortDesc.value 
                ? (fieldB - fieldA) 
                : (fieldA - fieldB);
        }
    });
    
    return result;
});

const maxUnitsSold = computed(() => {
    if (!props.productPerformance || props.productPerformance.length === 0) return 1;
    return Math.max(...props.productPerformance.map(item => item.units_sold), 1);
});

const topSoldProduct = computed(() => {
    if (!props.productPerformance || props.productPerformance.length === 0) return null;
    return props.productPerformance.reduce((prev, current) => (prev.units_sold > current.units_sold) ? prev : current);
});

const topRevenueProduct = computed(() => {
    if (!props.productPerformance || props.productPerformance.length === 0) return null;
    return props.productPerformance.reduce((prev, current) => (prev.total_revenue > current.total_revenue) ? prev : current);
});

const averageProfitMargin = computed(() => {
    if (!props.productPerformance || props.productPerformance.length === 0) return 0;
    const totalRev = props.productPerformance.reduce((sum, item) => sum + item.total_revenue, 0);
    const totalProf = props.productPerformance.reduce((sum, item) => sum + item.total_profit, 0);
    return totalRev > 0 ? (totalProf / totalRev) * 100 : 0;
});

const lowStockCount = computed(() => {
    if (!props.productPerformance) return 0;
    return props.productPerformance.filter(item => item.current_stock <= item.min_stock).length;
});

const inventoryStats = computed(() => {
    const movements = props.inventoryMovements || [];
    const incoming = movements.filter(m => {
        const t = m.type?.toLowerCase() || '';
        return t === 'incoming' || t === 'in' || t === 'masuk';
    }).length;
    const outgoing = movements.filter(m => {
        const t = m.type?.toLowerCase() || '';
        return t === 'outgoing' || t === 'out' || t === 'keluar';
    }).length;
    return { total: movements.length, incoming, outgoing };
});

const tab = ref('transaction_history');

const selectedFilter = ref(props.filters?.filter || 'All Time');
const dateDialog = ref(false);

const parseInitialDates = () => {
    const dates = [];
    if (props.filters?.start_date) {
        dates.push(new Date(props.filters.start_date));
    }
    if (props.filters?.end_date && props.filters.end_date !== props.filters.start_date) {
        dates.push(new Date(props.filters.end_date));
    }
    return dates;
};

const customDates = ref(parseInitialDates());

watch(selectedFilter, (newVal) => {
    if (newVal !== 'Custom') {
        router.get(route('report'), { filter: newVal }, { preserveState: true });
    }
});

const applyDate = () => {
    if (customDates.value && customDates.value.length > 0) {
        const sorted = [...customDates.value].sort((a, b) => a - b);
        const start_date = new Date(sorted[0].getTime() - (sorted[0].getTimezoneOffset() * 60000)).toISOString().split('T')[0];
        const end_date = new Date(sorted[sorted.length - 1].getTime() - (sorted[sorted.length - 1].getTimezoneOffset() * 60000)).toISOString().split('T')[0];

        selectedFilter.value = 'Custom';
        router.get(route('report'), { 
            filter: 'Custom', 
            start_date: start_date,
            end_date: end_date
        }, { preserveState: true });
        dateDialog.value = false;
    }
};

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

// --- Export Report Logic ---
const snackbar = ref(false);
const snackbarText = ref('');
const showToast = (msg) => {
    snackbarText.value = msg;
    snackbar.value = true;
};

const downloadCSV = (filename, headers, rows) => {
    const escapeCSV = (val) => {
        if (val === null || val === undefined) return '""';
        let str = String(val).replace(/"/g, '""');
        return `"${str}"`;
    };

    const headerLine = headers.map(escapeCSV).join(';');
    const contentLines = rows.map(row => row.map(escapeCSV).join(';'));
    const csvContent = "\uFEFF" + "sep=;\n" + [headerLine, ...contentLines].join('\n'); // Add UTF-8 BOM and sep=; for Excel compatibility

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    
    const dateStr = new Date().toISOString().split('T')[0];
    link.setAttribute('href', url);
    link.setAttribute('download', `${filename}_${dateStr}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const exportSalesReport = () => {
    const headers = ['Date', 'Transactions', 'Revenue', 'Profit', 'Avg Value'];
    const rows = (props.dailySales || []).map(day => [
        day.date,
        day.transactions,
        day.revenue,
        day.profit,
        day.avg_value
    ]);
    
    // Add total row at the end
    rows.push([
        'Total Penjualan',
        props.stats?.transactionsCount || 0,
        props.stats?.totalRevenue || 0,
        props.stats?.totalProfit || 0,
        '-'
    ]);
    
    downloadCSV('grocerypro_sales_report', headers, rows);
    showToast('Laporan Sales Report berhasil diekspor!');
};

const handleExport = () => {
    if (tab.value === 'sales_report') {
        exportSalesReport();
    } else if (tab.value === 'product_performance') {
        showToast('Export untuk tab Product Performance belum diimplementasikan.');
    } else if (tab.value === 'inventory_report') {
        showToast('Export untuk tab Inventory Report belum diimplementasikan.');
    } else if (tab.value === 'transaction_history') {
        showToast('Export untuk tab Transaction History belum diimplementasikan.');
    }
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

            <!-- Dynamic Summary Cards based on Tab -->
            <v-row class="mb-6">
                <!-- Default Cards (Sales & Transactions) -->
                <template v-if="tab === 'sales_report' || tab === 'transaction_history'">
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
                </template>

                <!-- Product Performance Cards -->
                <template v-else-if="tab === 'product_performance'">
                    <!-- Best Seller -->
                    <v-col cols="12" sm="6" md="3">
                        <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                            <v-card-text>
                                <div class="d-flex justify-space-between align-start">
                                    <div style="width: calc(100% - 48px)">
                                        <div class="text-caption text-grey-darken-1 font-weight-medium mb-1">Best Seller (Qty)</div>
                                        <div class="text-subtitle-1 font-weight-bold text-truncate" :title="topSoldProduct ? topSoldProduct.product_name : 'N/A'">
                                            {{ topSoldProduct ? topSoldProduct.product_name : 'N/A' }}
                                        </div>
                                        <div class="text-caption mt-2 text-primary font-weight-medium d-flex align-center">
                                            {{ topSoldProduct ? formatNumber(topSoldProduct.units_sold) : 0 }} {{ topSoldProduct ? topSoldProduct.unit : 'pcs' }} sold
                                        </div>
                                    </div>
                                    <v-avatar color="primary" size="40" rounded>
                                        <v-icon color="white">mdi-crown</v-icon>
                                    </v-avatar>
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>

                    <!-- Top Earner -->
                    <v-col cols="12" sm="6" md="3">
                        <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                            <v-card-text>
                                <div class="d-flex justify-space-between align-start">
                                    <div style="width: calc(100% - 48px)">
                                        <div class="text-caption text-grey-darken-1 font-weight-medium mb-1">Top Earner (Revenue)</div>
                                        <div class="text-subtitle-1 font-weight-bold text-truncate" :title="topRevenueProduct ? topRevenueProduct.product_name : 'N/A'">
                                            {{ topRevenueProduct ? topRevenueProduct.product_name : 'N/A' }}
                                        </div>
                                        <div class="text-caption mt-2 text-success font-weight-medium d-flex align-center">
                                            {{ topRevenueProduct ? formatCurrency(topRevenueProduct.total_revenue) : formatCurrency(0) }}
                                        </div>
                                    </div>
                                    <v-avatar color="primary" size="40" rounded>
                                        <v-icon color="white">mdi-cash-multiple</v-icon>
                                    </v-avatar>
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>

                    <!-- Avg Profit Margin -->
                    <v-col cols="12" sm="6" md="3">
                        <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                            <v-card-text>
                                <div class="d-flex justify-space-between align-start">
                                    <div>
                                        <div class="text-caption text-grey-darken-1 font-weight-medium mb-1">Avg Profit Margin</div>
                                        <div class="text-h5 font-weight-bold">
                                            {{ averageProfitMargin.toFixed(1) }}%
                                        </div>
                                        <div class="text-caption mt-2 text-success font-weight-medium d-flex align-center">
                                            Overall profitability rate
                                        </div>
                                    </div>
                                    <v-avatar color="primary" size="40" rounded>
                                        <v-icon color="white">mdi-percent</v-icon>
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
                                        <div class="text-h5 font-weight-bold" :class="lowStockCount > 0 ? 'text-error' : ''">
                                            {{ lowStockCount }}
                                        </div>
                                        <div class="text-caption mt-2 font-weight-medium d-flex align-center" :class="lowStockCount > 0 ? 'text-error' : 'text-grey'">
                                            {{ lowStockCount > 0 ? 'Needs restock' : 'All stock optimal' }}
                                        </div>
                                    </div>
                                    <v-avatar color="primary" size="40" rounded>
                                        <v-icon color="white">mdi-alert-octagon</v-icon>
                                    </v-avatar>
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </template>

                <!-- Inventory Report Cards -->
                <template v-else-if="tab === 'inventory_report'">
                    <!-- Total Movements -->
                    <v-col cols="12" sm="6" md="3">
                        <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                            <v-card-text>
                                <div class="d-flex justify-space-between align-start">
                                    <div>
                                        <div class="text-caption text-grey-darken-1 font-weight-medium mb-1">Total Movements</div>
                                        <div class="text-h5 font-weight-bold">
                                            {{ formatNumber(inventoryStats.total) }}
                                        </div>
                                        <div class="text-caption mt-2 text-primary font-weight-medium d-flex align-center">
                                            Inventory activities
                                        </div>
                                    </div>
                                    <v-avatar color="primary" size="40" rounded>
                                        <v-icon color="white">mdi-swap-horizontal</v-icon>
                                    </v-avatar>
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>

                    <!-- Incoming Items -->
                    <v-col cols="12" sm="6" md="3">
                        <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                            <v-card-text>
                                <div class="d-flex justify-space-between align-start">
                                    <div>
                                        <div class="text-caption text-grey-darken-1 font-weight-medium mb-1">Incoming Items</div>
                                        <div class="text-h5 font-weight-bold text-success">
                                            {{ formatNumber(inventoryStats.incoming) }}
                                        </div>
                                        <div class="text-caption mt-2 text-success font-weight-medium d-flex align-center">
                                            Stock additions
                                        </div>
                                    </div>
                                    <v-avatar color="primary" size="40" rounded>
                                        <v-icon color="white">mdi-package-down</v-icon>
                                    </v-avatar>
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>

                    <!-- Outgoing Items -->
                    <v-col cols="12" sm="6" md="3">
                        <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                            <v-card-text>
                                <div class="d-flex justify-space-between align-start">
                                    <div>
                                        <div class="text-caption text-grey-darken-1 font-weight-medium mb-1">Outgoing Items</div>
                                        <div class="text-h5 font-weight-bold text-error">
                                            {{ formatNumber(inventoryStats.outgoing) }}
                                        </div>
                                        <div class="text-caption mt-2 text-error font-weight-medium d-flex align-center">
                                            Stock deductions
                                        </div>
                                    </div>
                                    <v-avatar color="primary" size="40" rounded>
                                        <v-icon color="white">mdi-package-up</v-icon>
                                    </v-avatar>
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>

                    <!-- Stock Alerts -->
                    <v-col cols="12" sm="6" md="3">
                        <v-card class="rounded-lg elevation-1 h-100" variant="flat">
                            <v-card-text>
                                <div class="d-flex justify-space-between align-start">
                                    <div>
                                        <div class="text-caption text-grey-darken-1 font-weight-medium mb-1">Low Stock Alerts</div>
                                        <div class="text-h5 font-weight-bold" :class="lowStockCount > 0 ? 'text-error' : ''">
                                            {{ lowStockCount }}
                                        </div>
                                        <div class="text-caption mt-2 font-weight-medium d-flex align-center" :class="lowStockCount > 0 ? 'text-error' : 'text-grey'">
                                            Items below minimum
                                        </div>
                                    </div>
                                    <v-avatar color="primary" size="40" rounded>
                                        <v-icon color="white">mdi-bell-alert</v-icon>
                                    </v-avatar>
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </template>
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
                        v-model="selectedFilter"
                        :items="['All Time', 'Daily', 'Weekly', 'Monthly', 'Custom']"
                        variant="outlined"
                        density="compact"
                        hide-details
                        bg-color="surface"
                        class="rounded-lg"
                        style="width: 150px;"
                    ></v-select>
                    <v-btn
                        @click="dateDialog = true"
                        variant="outlined"
                        color="primary"
                        prepend-icon="mdi-calendar-range"
                        class="text-none font-weight-medium bg-surface border-grey-lighten-2 rounded-lg"
                        height="40"
                    >
                        Date Range
                    </v-btn>
                    <v-btn
                        @click="handleExport"
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
                    <v-card flat class="rounded-xl border mb-6 bg-surface">
                        <v-card-title class="text-subtitle-1 font-weight-medium pt-5 px-6 text-grey-darken-1" style="font-size: 0.9rem !important;">
                            Daily Sales Overview
                        </v-card-title>
                        
                        <v-divider class="mt-3 mb-0"></v-divider>
                        
                        <v-table hover density="comfortable">
                            <thead>
                                <tr>
                                    <th class="text-left font-weight-bold text-grey-darken-3 pl-6" style="font-size: 0.85rem;">Date</th>
                                    <th class="text-left font-weight-bold text-grey-darken-3" style="font-size: 0.85rem;">Transactions</th>
                                    <th class="text-left font-weight-bold text-grey-darken-3" style="font-size: 0.85rem;">Revenue</th>
                                    <th class="text-left font-weight-bold text-grey-darken-3" style="font-size: 0.85rem;">Profit</th>
                                    <th class="text-left font-weight-bold text-grey-darken-3 pr-6" style="font-size: 0.85rem;">Avg Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(day, idx) in dailySales" :key="idx" style="font-size: 0.85rem;">
                                    <td class="pl-6 font-weight-medium text-grey-darken-3">{{ day.date }}</td>
                                    <td class="text-grey-darken-1">{{ formatNumber(day.transactions) }}</td>
                                    <td class="text-grey-darken-3">{{ formatCurrency(day.revenue) }}</td>
                                    <td class="text-success">{{ formatCurrency(day.profit) }}</td>
                                    <td class="pr-6 text-grey-darken-1">{{ formatCurrency(day.avg_value) }}</td>
                                </tr>
                                <tr v-if="dailySales && dailySales.length > 0" class="bg-grey-lighten-4" style="font-size: 0.85rem;">
                                    <td class="pl-6 font-weight-bold text-grey-darken-3">Total Penjualan</td>
                                    <td class="font-weight-bold text-grey-darken-1">{{ formatNumber(stats.transactionsCount) }}</td>
                                    <td class="font-weight-bold text-primary">{{ formatCurrency(stats.totalRevenue) }}</td>
                                    <td class="font-weight-bold text-success">{{ formatCurrency(stats.totalProfit) }}</td>
                                    <td class="pr-6 font-weight-bold text-grey-darken-1">-</td>
                                </tr>
                                <tr v-if="!dailySales || dailySales.length === 0">
                                    <td colspan="5" class="text-center pa-4 text-grey">No sales data available.</td>
                                </tr>
                            </tbody>
                        </v-table>
                    </v-card>
                </v-window-item>
                
                <v-window-item v-if="isAdmin" value="product_performance">
                    <!-- Product Table Card -->
                    <v-card flat class="rounded-xl border mb-6 bg-surface">
                        <!-- Search and Controls inside the card header -->
                        <div class="px-6 py-4 d-flex flex-column flex-sm-row justify-space-between align-sm-center border-b ga-3">
                            <div>
                                <v-card-title class="text-subtitle-1 font-weight-bold pa-0 text-grey-darken-3">
                                    Product Sales & Profitability List
                                </v-card-title>
                                <span class="text-caption text-grey-darken-1">Interactive ranking table of product performance</span>
                            </div>
                            
                            <div class="d-flex align-center ga-3">
                                <v-text-field
                                    v-model="productSearch"
                                    prepend-inner-icon="mdi-magnify"
                                    placeholder="Cari produk..."
                                    variant="outlined"
                                    density="compact"
                                    hide-details
                                    bg-color="surface"
                                    class="rounded-lg"
                                    style="width: 250px;"
                                ></v-text-field>
                            </div>
                        </div>
                        
                        <v-table hover density="comfortable">
                            <thead>
                                <tr>
                                    <th @click="productSortBy === 'product_name' ? productSortDesc = !productSortDesc : (productSortBy = 'product_name', productSortDesc = false)" class="text-left font-weight-bold text-grey-darken-3 pl-6" style="font-size: 0.85rem; cursor: pointer; user-select: none;">
                                        Product Details
                                        <v-icon size="small" class="ml-1" v-if="productSortBy === 'product_name'">
                                            {{ productSortDesc ? 'mdi-chevron-down' : 'mdi-chevron-up' }}
                                        </v-icon>
                                    </th>
                                    <th @click="productSortBy === 'current_stock' ? productSortDesc = !productSortDesc : (productSortBy = 'current_stock', productSortDesc = true)" class="text-left font-weight-bold text-grey-darken-3" style="font-size: 0.85rem; cursor: pointer; user-select: none;">
                                        Stock Status
                                        <v-icon size="small" class="ml-1" v-if="productSortBy === 'current_stock'">
                                            {{ productSortDesc ? 'mdi-chevron-down' : 'mdi-chevron-up' }}
                                        </v-icon>
                                    </th>
                                    <th @click="productSortBy === 'units_sold' ? productSortDesc = !productSortDesc : (productSortBy = 'units_sold', productSortDesc = true)" class="text-left font-weight-bold text-grey-darken-3" style="font-size: 0.85rem; cursor: pointer; user-select: none;">
                                        Units Sold
                                        <v-icon size="small" class="ml-1" v-if="productSortBy === 'units_sold'">
                                            {{ productSortDesc ? 'mdi-chevron-down' : 'mdi-chevron-up' }}
                                        </v-icon>
                                    </th>
                                    <th @click="productSortBy === 'total_revenue' ? productSortDesc = !productSortDesc : (productSortBy = 'total_revenue', productSortDesc = true)" class="text-right font-weight-bold text-grey-darken-3" style="font-size: 0.85rem; cursor: pointer; user-select: none;">
                                        Revenue
                                        <v-icon size="small" class="ml-1" v-if="productSortBy === 'total_revenue'">
                                            {{ productSortDesc ? 'mdi-chevron-down' : 'mdi-chevron-up' }}
                                        </v-icon>
                                    </th>
                                    <th @click="productSortBy === 'total_profit' ? productSortDesc = !productSortDesc : (productSortBy = 'total_profit', productSortDesc = true)" class="text-right font-weight-bold text-grey-darken-3" style="font-size: 0.85rem; cursor: pointer; user-select: none;">
                                        Profit
                                        <v-icon size="small" class="ml-1" v-if="productSortBy === 'total_profit'">
                                            {{ productSortDesc ? 'mdi-chevron-down' : 'mdi-chevron-up' }}
                                        </v-icon>
                                    </th>
                                    <th class="text-right font-weight-bold text-grey-darken-3 pr-6" style="font-size: 0.85rem; user-select: none;">
                                        Margin
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in filteredProductPerformance" :key="item.sku" style="font-size: 0.85rem;">
                                    <!-- Product Details -->
                                    <td class="pl-6 py-3">
                                        <div class="d-flex flex-column">
                                            <span class="font-weight-bold text-grey-darken-3">{{ item.product_name }}</span>
                                            <span class="text-caption text-grey d-flex align-center mt-1">
                                                <span class="font-weight-medium bg-grey-lighten-3 px-1 rounded mr-2">{{ item.sku }}</span>
                                                <span>{{ item.category_name }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- Stock Status -->
                                    <td>
                                        <div class="d-flex align-center">
                                            <div class="d-flex flex-column mr-3">
                                                <span :class="item.current_stock <= item.min_stock ? 'text-error font-weight-bold' : 'text-grey-darken-3'">
                                                    {{ formatNumber(item.current_stock) }} {{ item.unit }}
                                                </span>
                                                <span class="text-caption text-grey">Min: {{ formatNumber(item.min_stock) }}</span>
                                            </div>
                                            <v-chip
                                                v-if="item.current_stock <= item.min_stock"
                                                color="error"
                                                size="x-small"
                                                variant="flat"
                                                class="font-weight-bold"
                                            >
                                                Low Stock
                                            </v-chip>
                                        </div>
                                    </td>
                                    
                                    <!-- Units Sold -->
                                    <td>
                                        <div class="d-flex align-center" style="min-width: 140px;">
                                            <span class="font-weight-bold mr-2 text-grey-darken-3">{{ formatNumber(item.units_sold) }}</span>
                                            <v-progress-linear
                                                :model-value="(item.units_sold / maxUnitsSold) * 100"
                                                color="primary"
                                                height="6"
                                                rounded
                                                class="flex-grow-1"
                                            ></v-progress-linear>
                                        </div>
                                    </td>
                                    
                                    <!-- Revenue -->
                                    <td class="text-right font-weight-medium text-grey-darken-3">
                                        {{ formatCurrency(item.total_revenue) }}
                                    </td>
                                    
                                    <!-- Profit -->
                                    <td class="text-right font-weight-medium text-success">
                                        {{ formatCurrency(item.total_profit) }}
                                    </td>
                                    
                                    <!-- Margin -->
                                    <td class="text-right pr-6 font-weight-bold" :class="item.total_revenue > 0 ? 'text-primary' : 'text-grey'">
                                        {{ item.total_revenue > 0 ? ((item.total_profit / item.total_revenue) * 100).toFixed(1) + '%' : '0%' }}
                                    </td>
                                </tr>
                                <tr v-if="filteredProductPerformance.length === 0">
                                    <td colspan="6" class="text-center pa-10 text-grey">
                                        <v-icon size="36" class="mb-2" color="grey-lighten-1">mdi-alert-circle-outline</v-icon>
                                        <div>Tidak ada data performa produk.</div>
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>
                    </v-card>
                </v-window-item>
                
                <v-window-item v-if="isAdmin" value="inventory_report">
                    <v-card flat class="rounded-xl border mb-6 bg-surface">
                        <v-card-title class="text-subtitle-1 font-weight-medium pt-5 px-6 text-grey-darken-1" style="font-size: 0.9rem !important;">
                            Inventory Status
                        </v-card-title>
                        
                        <v-divider class="mt-3 mb-0"></v-divider>
                        
                        <v-table hover density="comfortable">
                            <thead>
                                <tr>
                                    <th class="text-left font-weight-bold text-grey-darken-3 pl-6" style="font-size: 0.85rem;">Movement ID</th>
                                    <th class="text-left font-weight-bold text-grey-darken-3" style="font-size: 0.85rem;">Date</th>
                                    <th class="text-left font-weight-bold text-grey-darken-3" style="font-size: 0.85rem;">Type</th>
                                    <th class="text-left font-weight-bold text-grey-darken-3" style="font-size: 0.85rem;">Product</th>
                                    <th class="text-left font-weight-bold text-grey-darken-3" style="font-size: 0.85rem;">Quantity</th>
                                    <th class="text-left font-weight-bold text-grey-darken-3" style="font-size: 0.85rem;">Reference</th>
                                    <th class="text-left font-weight-bold text-grey-darken-3 pr-6" style="font-size: 0.85rem;">Supplier/Customer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="movement in inventoryMovements" :key="movement.id" style="font-size: 0.85rem;">
                                    <td class="pl-6 font-weight-medium text-grey-darken-3">{{ movement.id }}</td>
                                    <td class="text-grey-darken-1">{{ movement.date }}</td>
                                    <td>
                                        <div class="d-flex align-center" :class="['incoming', 'in', 'masuk'].includes(movement.type.toLowerCase()) ? 'text-success' : 'text-error'">
                                            <v-icon size="small" class="mr-1">
                                                {{ ['incoming', 'in', 'masuk'].includes(movement.type.toLowerCase()) ? 'mdi-package-down' : 'mdi-package-up' }}
                                            </v-icon>
                                            {{ movement.type }}
                                        </div>
                                    </td>
                                    <td class="text-grey-darken-3">{{ movement.product_name }}</td>
                                    <td>
                                        <span :class="['incoming', 'in', 'masuk'].includes(movement.type.toLowerCase()) ? 'text-success' : 'text-error'">
                                            {{ ['incoming', 'in', 'masuk'].includes(movement.type.toLowerCase()) ? '+' : '-' }}{{ movement.qty }} {{ movement.unit }}
                                        </span>
                                    </td>
                                    <td class="text-grey-darken-1">{{ movement.reference }}</td>
                                    <td class="pr-6 text-grey-darken-1">{{ movement.supplier }}</td>
                                </tr>
                            </tbody>
                        </v-table>
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

        <v-dialog v-model="dateDialog" max-width="400">
            <v-card class="rounded-xl">
                <v-card-title class="pa-4 font-weight-bold border-b">
                    Custom Date
                </v-card-title>
                <v-card-text class="pa-0">
                    <v-date-picker
                        v-model="customDates"
                        multiple="range"
                        color="primary"
                        hide-header
                        width="100%"
                    ></v-date-picker>
                </v-card-text>
                <v-card-actions class="pa-4 border-t">
                    <v-spacer></v-spacer>
                    <v-btn
                        variant="text"
                        class="text-none mr-2"
                        @click="dateDialog = false"
                    >
                        Cancel
                    </v-btn>
                    <v-btn
                        color="primary"
                        variant="flat"
                        class="text-none px-6"
                        @click="applyDate"
                    >
                        Apply
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Notification Snackbar -->
        <v-snackbar
            v-model="snackbar"
            timeout="3000"
            color="success"
            rounded="lg"
            elevation="3"
        >
            <div class="d-flex align-center ga-2">
                <v-icon color="white">mdi-check-circle</v-icon>
                <span class="font-weight-medium text-white">{{ snackbarText }}</span>
            </div>
            <template #actions>
                <v-btn
                    variant="text"
                    color="white"
                    icon="mdi-close"
                    size="small"
                    @click="snackbar = false"
                ></v-btn>
            </template>
        </v-snackbar>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    products: Array,
    categories: Array,
    stockMovements: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const addProductDialog = ref(false);
const addStockDialog = ref(false);
const snackbar = ref(false);
const snackbarMessage = ref('');

// Form for adding new product
const productForm = useForm({
    name: '',
    category_id: null,
    stock_qty: 0,
    unit: '',
    buy_price: 0,
    sell_price: 0,
    supplier: '',
});

// Form for adding incoming stock
const stockForm = useForm({
    product_id: null,
    qty: 0,
    supplier: '',
    date_received: new Date().toISOString().split('T')[0],
    total_cost: 0,
});

// Filtered products based on search
const filteredProducts = computed(() => {
    if (!search.value) return props.products;
    const query = search.value.toLowerCase();
    return props.products.filter(p => 
        p.name.toLowerCase().includes(query) || 
        p.sku.toLowerCase().includes(query)
    );
});

// Display values with thousand separator
const buyPriceDisplay = ref('0');
const sellPriceDisplay = ref('0');
const totalCostDisplay = ref('0');

// Format number with thousand separator (titik)
const formatNumber = (num) => {
    if (!num && num !== 0) return '';
    return new Intl.NumberFormat('id-ID').format(num);
};

// Parse formatted number back to raw number
const parseNumber = (str) => {
    if (!str) return 0;
    return parseFloat(String(str).replace(/\./g, '').replace(',', '.')) || 0;
};

// Handle input for buy_price
const onBuyPriceInput = (e) => {
    const raw = parseNumber(e.target.value);
    productForm.buy_price = raw;
    buyPriceDisplay.value = formatNumber(raw);
};

// Handle input for sell_price
const onSellPriceInput = (e) => {
    const raw = parseNumber(e.target.value);
    productForm.sell_price = raw;
    sellPriceDisplay.value = formatNumber(raw);
};

// Handle input for total_cost
const onTotalCostInput = (e) => {
    const raw = parseNumber(e.target.value);
    stockForm.total_cost = raw;
    totalCostDisplay.value = formatNumber(raw);
};

const formatPrice = (price) => {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-CA');
};

const formatProductId = (id) => {
    return 'PRD-' + String(id).padStart(3, '0');
};

const formatStockId = (id) => {
    return 'IN-' + String(id).padStart(3, '0');
};

const getStatusColor = (product) => {
    if (product.stock_qty <= 0) return 'error';
    if (product.stock_qty <= product.min_stock) return 'error';
    return '#4A7C4E';
};

const getStatusLabel = (product) => {
    if (product.stock_qty <= 0) return 'Out of Stock';
    if (product.stock_qty <= product.min_stock) return 'Low Stock';
    return 'Available';
};

const submitProduct = () => {
    productForm.post(route('products.store'), {
        preserveScroll: true,
        onSuccess: () => {
            addProductDialog.value = false;
            productForm.reset();
            buyPriceDisplay.value = '0';
            sellPriceDisplay.value = '0';
            snackbarMessage.value = 'Product added successfully!';
            snackbar.value = true;
        },
        onError: () => {
            snackbarMessage.value = 'Failed to add product.';
            snackbar.value = true;
        }
    });
};

const submitStock = () => {
    stockForm.post(route('products.add-stock'), {
        preserveScroll: true,
        onSuccess: () => {
            addStockDialog.value = false;
            stockForm.reset();
            stockForm.date_received = new Date().toISOString().split('T')[0];
            totalCostDisplay.value = '0';
            snackbarMessage.value = 'Stock added successfully!';
            snackbar.value = true;
        },
        onError: () => {
            snackbarMessage.value = 'Failed to add stock.';
            snackbar.value = true;
        }
    });
};
</script>

<template>
    <Head title="Goods/Stock Management" />

    <AuthenticatedLayout>
        <template #header-title>
            Goods/Stock Management
        </template>

        <template #header-description>
            <p class="text-sm">
                Manage your inventory and product stock levels
            </p>
        </template>

        <template #header-actions>
            <v-btn variant="outlined" color="grey-darken-2" rounded="lg" class="text-none mr-2" height="40">
                <v-icon start size="small">mdi-download</v-icon>
                Export
            </v-btn>
        </template>

        <v-container fluid class="pa-0 mt-4">
            <!-- Search and Action Buttons -->
            <v-row class="mb-4" align="center">
                <v-col cols="12" md="6">
                    <v-text-field
                        v-model="search"
                        prepend-inner-icon="mdi-magnify"
                        placeholder="Search products..."
                        variant="outlined"
                        density="compact"
                        hide-details
                        rounded="lg"
                    ></v-text-field>
                </v-col>
                <v-spacer></v-spacer>
                <v-col cols="auto">
                    <v-btn 
                        variant="outlined" 
                        color="grey-darken-2" 
                        rounded="lg" 
                        class="text-none mr-2" 
                        height="40"
                        @click="addStockDialog = true"
                    >
                        <v-icon start size="small">mdi-package-variant-plus</v-icon>
                        Incoming Stock
                    </v-btn>
                    <v-btn 
                        color="primary" 
                        rounded="lg" 
                        class="text-none" 
                        height="40"
                        @click="addProductDialog = true"
                    >
                        <v-icon start size="small">mdi-plus</v-icon>
                        Add Product
                    </v-btn>
                </v-col>
            </v-row>

            <!-- Products Table -->
            <v-card class="rounded-xl mb-6 border" elevation="0">
                <v-table hover>
                    <thead>
                        <tr>
                            <th class="text-left font-weight-bold text-grey-darken-2">Product ID</th>
                            <th class="text-left font-weight-bold text-grey-darken-2">Name</th>
                            <th class="text-left font-weight-bold text-grey-darken-2">Category</th>
                            <th class="text-left font-weight-bold text-grey-darken-2">Stock</th>
                            <th class="text-left font-weight-bold text-grey-darken-2">Buy Price</th>
                            <th class="text-left font-weight-bold text-grey-darken-2">Sell Price</th>
                            <th class="text-left font-weight-bold text-grey-darken-2">Status</th>
                            <th class="text-center font-weight-bold text-grey-darken-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in filteredProducts" :key="product.id">
                            <td class="font-weight-medium">{{ product.sku }}</td>
                            <td>
                                <div class="d-flex align-center">
                                    <v-icon size="small" color="primary" class="mr-2">mdi-package-variant</v-icon>
                                    {{ product.name }}
                                </div>
                            </td>
                            <td class="text-grey-darken-1">{{ product.category?.name || '-' }}</td>
                            <td>{{ product.stock_qty }} {{ product.unit || 'pcs' }}</td>
                            <td>{{ formatPrice(product.buy_price) }}</td>
                            <td class="font-weight-medium">{{ formatPrice(product.sell_price) }}</td>
                            <td>
                                <v-chip
                                    size="small"
                                    :color="getStatusColor(product)"
                                    variant="flat"
                                    class="font-weight-medium"
                                >
                                    {{ getStatusLabel(product) }}
                                </v-chip>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex align-center ga-1">
                                    <v-btn icon size="small" variant="text" color="grey-darken-2">
                                        <v-icon size="18">mdi-square-edit-outline</v-icon>
                                    </v-btn>
                                    <v-btn icon size="small" variant="text" color="error">
                                        <v-icon size="18">mdi-delete-outline</v-icon>
                                    </v-btn>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredProducts.length === 0">
                            <td colspan="8" class="text-center py-8 text-grey">
                                No products found.
                            </td>
                        </tr>
                    </tbody>
                </v-table>
            </v-card>

            <!-- Incoming Stock History -->
            <v-card class="rounded-xl border" elevation="0">
                <v-card-title class="pa-4 font-weight-bold">
                    Incoming Stock History
                </v-card-title>
                <v-divider></v-divider>
                <v-table hover>
                    <thead>
                        <tr>
                            <th class="text-left font-weight-bold text-grey-darken-2">Stock ID</th>
                            <th class="text-left font-weight-bold text-grey-darken-2">Date</th>
                            <th class="text-left font-weight-bold text-grey-darken-2">Product Name</th>
                            <th class="text-left font-weight-bold text-grey-darken-2">Quantity</th>
                            <th class="text-left font-weight-bold text-grey-darken-2">Supplier</th>
                            <th class="text-left font-weight-bold text-grey-darken-2">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="movement in stockMovements" :key="movement.id">
                            <td class="font-weight-medium">{{ formatStockId(movement.id) }}</td>
                            <td>{{ formatDate(movement.created_at) }}</td>
                            <td>
                                <div class="d-flex align-center">
                                    <v-icon size="small" color="primary" class="mr-2">mdi-package-variant</v-icon>
                                    {{ movement.product?.name || '-' }}
                                </div>
                            </td>
                            <td class="text-success font-weight-medium">+{{ movement.qty }} {{ movement.product?.unit || 'pcs' }}</td>
                            <td>{{ movement.supplier || '-' }}</td>
                            <td class="font-weight-medium">{{ formatPrice(movement.total_cost || 0) }}</td>
                        </tr>
                        <tr v-if="!stockMovements || stockMovements.length === 0">
                            <td colspan="6" class="text-center py-8 text-grey">
                                No incoming stock history.
                            </td>
                        </tr>
                    </tbody>
                </v-table>
            </v-card>
        </v-container>

        <!-- Add Product Dialog -->
        <v-dialog v-model="addProductDialog" max-width="600" persistent>
            <v-card class="rounded-xl">
                <v-card-title class="pa-4 font-weight-bold d-flex justify-space-between align-center">
                    Add New Product
                    <v-btn icon="mdi-close" variant="text" size="small" @click="addProductDialog = false"></v-btn>
                </v-card-title>
                <v-card-subtitle class="px-4 pb-4 text-grey">
                    Enter the product details to add it to your inventory
                </v-card-subtitle>
                
                <v-card-text class="pa-4">
                    <v-row>
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Product Name</div>
                            <v-text-field
                                v-model="productForm.name"
                                variant="outlined"
                                density="comfortable"
                                placeholder="Enter product name"
                                :error-messages="productForm.errors.name"
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Category</div>
                            <v-select
                                v-model="productForm.category_id"
                                :items="categories"
                                item-title="name"
                                item-value="id"
                                variant="outlined"
                                density="comfortable"
                                placeholder="Select category"
                                :error-messages="productForm.errors.category_id"
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-select>
                        </v-col>
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Initial Stock</div>
                            <v-text-field
                                v-model="productForm.stock_qty"
                                type="number"
                                step="0.01"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                :error-messages="productForm.errors.stock_qty"
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Unit</div>
                            <v-text-field
                                v-model="productForm.unit"
                                variant="outlined"
                                density="comfortable"
                                placeholder="pcs, kg, bottle, etc."
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Buy Price</div>
                            <v-text-field
                                :model-value="buyPriceDisplay"
                                @input="onBuyPriceInput"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                :error-messages="productForm.errors.buy_price"
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Sell Price</div>
                            <v-text-field
                                :model-value="sellPriceDisplay"
                                @input="onSellPriceInput"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                :error-messages="productForm.errors.sell_price"
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Supplier</div>
                            <v-text-field
                                v-model="productForm.supplier"
                                variant="outlined"
                                density="comfortable"
                                placeholder="Enter supplier name"
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                    </v-row>
                </v-card-text>
                
                <v-card-actions class="pa-4 pt-0">
                    <v-spacer></v-spacer>
                    <v-btn
                        variant="text"
                        color="grey-darken-1"
                        @click="addProductDialog = false"
                        rounded="lg"
                        class="px-4 text-none"
                    >
                        Cancel
                    </v-btn>
                    <v-btn
                        color="#4A7C4E"
                        @click="submitProduct"
                        :loading="productForm.processing"
                        rounded="lg"
                        class="px-4 text-none"
                        variant="flat"
                    >
                        Add Product
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Add Incoming Stock Dialog -->
        <v-dialog v-model="addStockDialog" max-width="500" persistent>
            <v-card class="rounded-xl">
                <v-card-title class="pa-4 font-weight-bold d-flex justify-space-between align-center">
                    Add Incoming Stock
                    <v-btn icon="mdi-close" variant="text" size="small" @click="addStockDialog = false"></v-btn>
                </v-card-title>
                <v-card-subtitle class="px-4 pb-4 text-grey">
                    Record new stock received from supplier
                </v-card-subtitle>
                
                <v-card-text class="pa-4">
                    <div class="mb-2 text-subtitle-2 font-weight-medium">Product</div>
                    <v-select
                        v-model="stockForm.product_id"
                        :items="products"
                        item-title="name"
                        item-value="id"
                        variant="outlined"
                        density="comfortable"
                        placeholder="Select product"
                        :error-messages="stockForm.errors.product_id"
                        rounded="lg"
                        bg-color="#F5F5F5"
                        class="mb-4"
                    ></v-select>

                    <div class="mb-2 text-subtitle-2 font-weight-medium">Quantity</div>
                    <v-text-field
                        v-model="stockForm.qty"
                        type="number"
                        step="0.01"
                        variant="outlined"
                        density="comfortable"
                        placeholder="0"
                        :error-messages="stockForm.errors.qty"
                        rounded="lg"
                        bg-color="#F5F5F5"
                        class="mb-4"
                    ></v-text-field>

                    <div class="mb-2 text-subtitle-2 font-weight-medium">Supplier</div>
                    <v-text-field
                        v-model="stockForm.supplier"
                        variant="outlined"
                        density="comfortable"
                        placeholder="Enter supplier name"
                        rounded="lg"
                        bg-color="#F5F5F5"
                        class="mb-4"
                    ></v-text-field>

                    <div class="mb-2 text-subtitle-2 font-weight-medium">Date Received</div>
                    <v-text-field
                        v-model="stockForm.date_received"
                        type="date"
                        variant="outlined"
                        density="comfortable"
                        :error-messages="stockForm.errors.date_received"
                        rounded="lg"
                        bg-color="#F5F5F5"
                        class="mb-4"
                    ></v-text-field>

                    <div class="mb-2 text-subtitle-2 font-weight-medium">Total Cost</div>
                    <v-text-field
                        :model-value="totalCostDisplay"
                        @input="onTotalCostInput"
                        variant="outlined"
                        density="comfortable"
                        placeholder="0"
                        :error-messages="stockForm.errors.total_cost"
                        rounded="lg"
                        bg-color="#F5F5F5"
                    ></v-text-field>
                </v-card-text>
                
                <v-card-actions class="pa-4 pt-0">
                    <v-spacer></v-spacer>
                    <v-btn
                        variant="text"
                        color="grey-darken-1"
                        @click="addStockDialog = false"
                        rounded="lg"
                        class="px-4 text-none"
                    >
                        Cancel
                    </v-btn>
                    <v-btn
                        color="#4A7C4E"
                        @click="submitStock"
                        :loading="stockForm.processing"
                        rounded="lg"
                        class="px-4 text-none"
                        variant="flat"
                    >
                        Add Stock
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Snackbar -->
        <v-snackbar v-model="snackbar" :timeout="3000" color="success" location="bottom right">
            {{ snackbarMessage }}
        </v-snackbar>

    </AuthenticatedLayout>
</template>

<style scoped>
</style>

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
const editProductDialog = ref(false);
const addStockDialog = ref(false);
const snackbar = ref(false);
const snackbarMessage = ref('');
const editingProduct = ref(null);

// Form for adding new product
const productForm = useForm({
    name: '',
    category_id: null,
    stock_qty: 0,
    min_stock: 0,
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

// Form for editing product
const editForm = useForm({
    name: '',
    category_id: null,
    unit: '',
    buy_price: 0,
    sell_price: 0,
    min_stock: 0,
    stock_qty: 0,
});

// Display values for edit form
const editBuyPriceDisplay = ref('0');
const editSellPriceDisplay = ref('0');
const editMinStockDisplay = ref('0');
const editStockQtyDisplay = ref('0');
const stockQtyDisplay = ref('0');
const minStockDisplay = ref('0');
const qtyDisplay = ref('0');

// Real-time validation errors
const validationErrors = ref({
    productName: '',
    productSupplier: '',
    editName: '',
    stockSupplier: '',
});

// Validate name (must contain at least one letter)
const validateName = (value, field) => {
    if (!value) {
        validationErrors.value[field] = '';
        return;
    }
    if (!/[a-zA-Z]/.test(value)) {
        validationErrors.value[field] = 'Must contain at least one letter, cannot be numbers only.';
    } else {
        validationErrors.value[field] = '';
    }
};

// Watch for product name changes
watch(() => productForm.name, (val) => validateName(val, 'productName'));
watch(() => productForm.supplier, (val) => validateName(val, 'productSupplier'));
watch(() => editForm.name, (val) => validateName(val, 'editName'));
watch(() => stockForm.supplier, (val) => validateName(val, 'stockSupplier'));

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

// Handle input for edit buy_price
const onEditBuyPriceInput = (e) => {
    const raw = parseNumber(e.target.value);
    editForm.buy_price = raw;
    editBuyPriceDisplay.value = formatNumber(raw);
};

// Handle input for edit sell_price
const onEditSellPriceInput = (e) => {
    const raw = parseNumber(e.target.value);
    editForm.sell_price = raw;
    editSellPriceDisplay.value = formatNumber(raw);
};

// Format number with decimal support (for stock quantities)
const formatNumberDecimal = (num) => {
    if (!num && num !== 0) return '0';
    // Check if it has decimal
    if (num % 1 !== 0) {
        return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 }).format(num);
    }
    return new Intl.NumberFormat('id-ID').format(num);
};

// Parse number that may contain decimal (comma as decimal separator in ID locale)
const parseNumberDecimal = (str) => {
    if (!str) return 0;
    // Remove thousand separator (.) but keep decimal separator (,) and convert to .
    const cleaned = String(str).replace(/\./g, '').replace(',', '.');
    return parseFloat(cleaned) || 0;
};

// Handle input for edit min_stock - supports decimal with thousand separator
const onEditMinStockInput = (e) => {
    const raw = parseNumberDecimal(e.target.value);
    editForm.min_stock = raw;
    editMinStockDisplay.value = formatNumberDecimal(raw);
};

// Handle input for min_stock (Add Product) - supports decimal with thousand separator
const onMinStockInput = (e) => {
    const raw = parseNumberDecimal(e.target.value);
    productForm.min_stock = raw;
    minStockDisplay.value = formatNumberDecimal(raw);
};

// Handle input for stock_qty (Add Product) - supports decimal with thousand separator
const onStockQtyInput = (e) => {
    const raw = parseNumberDecimal(e.target.value);
    productForm.stock_qty = raw;
    stockQtyDisplay.value = formatNumberDecimal(raw);
};

// Handle input for qty (Add Stock) - supports decimal with thousand separator
const onQtyInput = (e) => {
    const raw = parseNumberDecimal(e.target.value);
    stockForm.qty = raw;
    qtyDisplay.value = formatNumberDecimal(raw);
};

// Handle input for edit stock_qty - supports decimal with thousand separator
const onEditStockQtyInput = (e) => {
    const raw = parseNumberDecimal(e.target.value);
    editForm.stock_qty = raw;
    editStockQtyDisplay.value = formatNumberDecimal(raw);
};

// Open edit dialog
const openEditDialog = (product) => {
    editingProduct.value = product;
    editForm.name = product.name;
    editForm.category_id = product.category_id;
    editForm.unit = product.unit || 'pcs';
    editForm.buy_price = product.buy_price;
    editForm.sell_price = product.sell_price;
    editForm.min_stock = product.min_stock;
    editForm.stock_qty = product.stock_qty;
    editBuyPriceDisplay.value = formatNumber(product.buy_price);
    editSellPriceDisplay.value = formatNumber(product.sell_price);
    editMinStockDisplay.value = formatNumberDecimal(product.min_stock);
    editStockQtyDisplay.value = formatNumberDecimal(product.stock_qty);
    validationErrors.value.editName = '';
    editProductDialog.value = true;
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
            stockQtyDisplay.value = '0';
            minStockDisplay.value = '0';
            validationErrors.value.productName = '';
            validationErrors.value.productSupplier = '';
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
            qtyDisplay.value = '0';
            validationErrors.value.stockSupplier = '';
            snackbarMessage.value = 'Stock added successfully!';
            snackbar.value = true;
        },
        onError: () => {
            snackbarMessage.value = 'Failed to add stock.';
            snackbar.value = true;
        }
    });
};

const submitEditProduct = () => {
    editForm.put(route('products.update', editingProduct.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            editProductDialog.value = false;
            editForm.reset();
            editBuyPriceDisplay.value = '0';
            editSellPriceDisplay.value = '0';
            editMinStockDisplay.value = '0';
            editStockQtyDisplay.value = '0';
            editingProduct.value = null;
            validationErrors.value.editName = '';
            snackbarMessage.value = 'Product updated successfully!';
            snackbar.value = true;
        },
        onError: () => {
            snackbarMessage.value = 'Failed to update product.';
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
                        color="#C4956A" 
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
                                    <v-btn icon size="small" variant="text" color="grey-darken-2" @click="openEditDialog(product)">
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
                    <!-- Product Information Section -->
                    <div class="text-subtitle-2 font-weight-bold text-grey-darken-2 mb-3">
                        <v-icon size="small" class="mr-1">mdi-package-variant</v-icon>
                        Product Information
                    </div>
                    <v-row class="mb-2">
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Product Name</div>
                            <v-text-field
                                v-model="productForm.name"
                                variant="outlined"
                                density="comfortable"
                                placeholder="Enter product name"
                                :error-messages="validationErrors.productName || productForm.errors.name"
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
                    </v-row>

                    <v-divider class="my-4"></v-divider>

                    <!-- Stock Information Section -->
                    <div class="text-subtitle-2 font-weight-bold text-grey-darken-2 mb-3">
                        <v-icon size="small" class="mr-1">mdi-warehouse</v-icon>
                        Stock Information
                    </div>
                    <v-row class="mb-2">
                        <v-col cols="4">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Initial Stock</div>
                            <v-text-field
                                :model-value="stockQtyDisplay"
                                @input="onStockQtyInput"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                :error-messages="productForm.errors.stock_qty"
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="4">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Min Stock</div>
                            <v-text-field
                                :model-value="minStockDisplay"
                                @input="onMinStockInput"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                :error-messages="productForm.errors.min_stock"
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="4">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Unit</div>
                            <v-text-field
                                v-model="productForm.unit"
                                variant="outlined"
                                density="comfortable"
                                placeholder="pcs, kg, etc."
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                    </v-row>

                    <v-divider class="my-4"></v-divider>

                    <!-- Pricing Section -->
                    <div class="text-subtitle-2 font-weight-bold text-grey-darken-2 mb-3">
                        <v-icon size="small" class="mr-1">mdi-currency-usd</v-icon>
                        Pricing
                    </div>
                    <v-row class="mb-2">
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Buy Price</div>
                            <v-text-field
                                :model-value="buyPriceDisplay"
                                @input="onBuyPriceInput"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                prefix="Rp"
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
                                prefix="Rp"
                                :error-messages="productForm.errors.sell_price"
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                    </v-row>

                    <v-divider class="my-4"></v-divider>

                    <!-- Supplier Section -->
                    <div class="text-subtitle-2 font-weight-bold text-grey-darken-2 mb-3">
                        <v-icon size="small" class="mr-1">mdi-truck-delivery</v-icon>
                        Supplier
                    </div>
                    <v-row>
                        <v-col cols="12">
                            <v-text-field
                                v-model="productForm.supplier"
                                variant="outlined"
                                density="comfortable"
                                placeholder="Enter supplier name"
                                :error-messages="validationErrors.productSupplier || productForm.errors.supplier"
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
                        color="#C4956A"
                        @click="submitProduct"
                        :loading="productForm.processing"
                        :disabled="!!validationErrors.productName || !!validationErrors.productSupplier"
                        rounded="lg"
                        class="px-4 text-none"
                        variant="flat"
                    >
                        Add Product
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Edit Product Dialog -->
        <v-dialog v-model="editProductDialog" max-width="600" persistent>
            <v-card class="rounded-xl">
                <v-card-title class="pa-4 font-weight-bold d-flex justify-space-between align-center">
                    Edit Product
                    <v-btn icon="mdi-close" variant="text" size="small" @click="editProductDialog = false"></v-btn>
                </v-card-title>
                <v-card-subtitle class="px-4 pb-4 text-grey">
                    Update the product information
                </v-card-subtitle>
                
                <v-card-text class="pa-4">
                    <!-- Product Information Section -->
                    <div class="text-subtitle-2 font-weight-bold text-grey-darken-2 mb-3">
                        <v-icon size="small" class="mr-1">mdi-package-variant</v-icon>
                        Product Information
                    </div>
                    <v-row class="mb-2">
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Product Name</div>
                            <v-text-field
                                v-model="editForm.name"
                                variant="outlined"
                                density="comfortable"
                                placeholder="Enter product name"
                                :error-messages="validationErrors.editName || editForm.errors.name"
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Category</div>
                            <v-select
                                v-model="editForm.category_id"
                                :items="categories"
                                item-title="name"
                                item-value="id"
                                variant="outlined"
                                density="comfortable"
                                placeholder="Select category"
                                :error-messages="editForm.errors.category_id"
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-select>
                        </v-col>
                    </v-row>

                    <v-divider class="my-4"></v-divider>

                    <!-- Stock Information Section -->
                    <div class="text-subtitle-2 font-weight-bold text-grey-darken-2 mb-3">
                        <v-icon size="small" class="mr-1">mdi-warehouse</v-icon>
                        Stock Information
                    </div>
                    <v-row class="mb-2">
                        <v-col cols="4">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Current Stock</div>
                            <v-text-field
                                :model-value="editStockQtyDisplay"
                                @input="onEditStockQtyInput"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                :error-messages="editForm.errors.stock_qty"
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="4">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Min Stock</div>
                            <v-text-field
                                :model-value="editMinStockDisplay"
                                @input="onEditMinStockInput"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                :error-messages="editForm.errors.min_stock"
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="4">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Unit</div>
                            <v-text-field
                                v-model="editForm.unit"
                                variant="outlined"
                                density="comfortable"
                                placeholder="pcs, kg, etc."
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                    </v-row>

                    <v-divider class="my-4"></v-divider>

                    <!-- Pricing Section -->
                    <div class="text-subtitle-2 font-weight-bold text-grey-darken-2 mb-3">
                        <v-icon size="small" class="mr-1">mdi-currency-usd</v-icon>
                        Pricing
                    </div>
                    <v-row>
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Buy Price</div>
                            <v-text-field
                                :model-value="editBuyPriceDisplay"
                                @input="onEditBuyPriceInput"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                prefix="Rp"
                                :error-messages="editForm.errors.buy_price"
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Sell Price</div>
                            <v-text-field
                                :model-value="editSellPriceDisplay"
                                @input="onEditSellPriceInput"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                prefix="Rp"
                                :error-messages="editForm.errors.sell_price"
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
                        @click="editProductDialog = false"
                        rounded="lg"
                        class="px-4 text-none"
                    >
                        Cancel
                    </v-btn>
                    <v-btn
                        color="#C4956A"
                        @click="submitEditProduct"
                        :loading="editForm.processing"
                        :disabled="!!validationErrors.editName"
                        rounded="lg"
                        class="px-4 text-none"
                        variant="flat"
                    >
                        Save Changes
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
                    <!-- Product Selection Section -->
                    <div class="text-subtitle-2 font-weight-bold text-grey-darken-2 mb-3">
                        <v-icon size="small" class="mr-1">mdi-package-variant</v-icon>
                        Product Selection
                    </div>
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
                    ></v-select>

                    <v-divider class="my-4"></v-divider>

                    <!-- Stock Details Section -->
                    <div class="text-subtitle-2 font-weight-bold text-grey-darken-2 mb-3">
                        <v-icon size="small" class="mr-1">mdi-warehouse</v-icon>
                        Stock Details
                    </div>
                    <v-row class="mb-2">
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Quantity</div>
                            <v-text-field
                                :model-value="qtyDisplay"
                                @input="onQtyInput"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                :error-messages="stockForm.errors.qty"
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Date Received</div>
                            <v-text-field
                                v-model="stockForm.date_received"
                                type="date"
                                variant="outlined"
                                density="comfortable"
                                :error-messages="stockForm.errors.date_received"
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                    </v-row>

                    <v-divider class="my-4"></v-divider>

                    <!-- Supplier & Cost Section -->
                    <div class="text-subtitle-2 font-weight-bold text-grey-darken-2 mb-3">
                        <v-icon size="small" class="mr-1">mdi-truck-delivery</v-icon>
                        Supplier & Cost
                    </div>
                    <v-row>
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Supplier</div>
                            <v-text-field
                                v-model="stockForm.supplier"
                                variant="outlined"
                                density="comfortable"
                                placeholder="Enter supplier name"
                                :error-messages="validationErrors.stockSupplier || stockForm.errors.supplier"
                                rounded="lg"
                                bg-color="#F5F5F5"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Total Cost</div>
                            <v-text-field
                                :model-value="totalCostDisplay"
                                @input="onTotalCostInput"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                prefix="Rp"
                                :error-messages="stockForm.errors.total_cost"
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
                        @click="addStockDialog = false"
                        rounded="lg"
                        class="px-4 text-none"
                    >
                        Cancel
                    </v-btn>
                    <v-btn
                        color="#C4956A"
                        @click="submitStock"
                        :loading="stockForm.processing"
                        :disabled="!!validationErrors.stockSupplier"
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

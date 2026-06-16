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
const tab = ref('products');
const selectedCategory = ref(null);
const productSortBy = ref('created_at');
const productSortDesc = ref(true);
const productPage = ref(1);
const itemsPerPage = ref(10);
const stockSortBy = ref('created_at');
const stockSortDesc = ref(true);
const stockPage = ref(1);
const stockItemsPerPage = ref(10);
const addProductDialog = ref(false);
const editProductDialog = ref(false);
const addStockDialog = ref(false);
const deleteDialog = ref(false);
const snackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');
const editingProduct = ref(null);
const deletingProduct = ref(null);
const productImagePreview = ref(null);
const editImagePreview = ref(null);

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
    image: null,
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
    image: null,
    _method: 'put',
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

const productSortOptions = {
    sku: product => product.sku || '',
    name: product => product.name || '',
    category: product => product.category?.name || '',
    stock_qty: product => Number(product.stock_qty || 0),
    min_stock: product => Number(product.min_stock || 0),
    buy_price: product => Number(product.buy_price || 0),
    sell_price: product => Number(product.sell_price || 0),
    status: product => getStatusLabel(product),
    created_at: product => product.created_at || '',
};

const stockSortOptions = {
    id: movement => Number(movement.id || 0),
    created_at: movement => movement.created_at || '',
    product_name: movement => movement.product?.name || '',
    qty: movement => Number(movement.qty || 0),
    supplier: movement => movement.supplier || '',
    total_cost: movement => Number(movement.total_cost || 0),
};

const showSnackbar = (message, color = 'success') => {
    snackbarMessage.value = message;
    snackbarColor.value = color;
    snackbar.value = true;
};

const normalizeFile = (file) => {
    if (Array.isArray(file)) {
        return file[0] || null;
    }

    return file || null;
};

const revokePreview = (preview) => {
    if (preview?.startsWith('blob:')) {
        URL.revokeObjectURL(preview);
    }
};

const setProductImage = (file) => {
    revokePreview(productImagePreview.value);

    const image = normalizeFile(file);
    productForm.image = image;
    productImagePreview.value = image ? URL.createObjectURL(image) : null;
};

const setEditImage = (file) => {
    revokePreview(editImagePreview.value?.startsWith('blob:') ? editImagePreview.value : null);

    const image = normalizeFile(file);
    editForm.image = image;
    editImagePreview.value = image ? URL.createObjectURL(image) : (editingProduct.value?.image_url || null);
};

const resetProductImage = () => {
    revokePreview(productImagePreview.value);
    productForm.image = null;
    productImagePreview.value = null;
};

const resetEditImage = () => {
    revokePreview(editImagePreview.value?.startsWith('blob:') ? editImagePreview.value : null);
    editForm.image = null;
    editImagePreview.value = null;
};

const openAddProductDialog = () => {
    resetProductImage();
    addProductDialog.value = true;
};

const closeAddProductDialog = () => {
    addProductDialog.value = false;
    resetProductImage();
};

const closeEditProductDialog = () => {
    editProductDialog.value = false;
    resetEditImage();
};

const sortProducts = (field) => {
    if (productSortBy.value === field) {
        productSortDesc.value = !productSortDesc.value;
    } else {
        productSortBy.value = field;
        productSortDesc.value = false;
    }
};

const getSortIcon = (field) => {
    if (productSortBy.value !== field) return 'mdi-swap-vertical';
    return productSortDesc.value ? 'mdi-arrow-down' : 'mdi-arrow-up';
};

const sortStockMovements = (field) => {
    if (stockSortBy.value === field) {
        stockSortDesc.value = !stockSortDesc.value;
    } else {
        stockSortBy.value = field;
        stockSortDesc.value = false;
    }
};

const getStockSortIcon = (field) => {
    if (stockSortBy.value !== field) return 'mdi-swap-vertical';
    return stockSortDesc.value ? 'mdi-arrow-down' : 'mdi-arrow-up';
};

// Rules for greater than zero validation
const greaterThanZeroRule = [
    v => {
        if (!v && v !== 0) return 'This field is required';
        const val = parseNumberDecimal(v);
        return val > 0 || 'Value must be greater than 0';
    }
];

const priceGreaterThanZeroRule = [
    v => {
        if (!v && v !== 0) return 'This field is required';
        const val = parseNumber(v);
        return val > 0 || 'Value must be greater than 0';
    }
];

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

// Watch for product unit changes to handle decimal rules dynamically
watch(() => productForm.unit, (newUnit) => {
    const lowerUnit = String(newUnit || '').toLowerCase();
    const isDecimalAllowed = lowerUnit === 'kg' || lowerUnit === 'liter' || lowerUnit === 'l';
    if (!isDecimalAllowed) {
        productForm.stock_qty = Math.round(productForm.stock_qty);
        productForm.min_stock = Math.round(productForm.min_stock);
        stockQtyDisplay.value = String(productForm.stock_qty);
        minStockDisplay.value = String(productForm.min_stock);
    }
});

watch(() => editForm.unit, (newUnit) => {
    const lowerUnit = String(newUnit || '').toLowerCase();
    const isDecimalAllowed = lowerUnit === 'kg' || lowerUnit === 'liter' || lowerUnit === 'l';
    if (!isDecimalAllowed) {
        editForm.stock_qty = Math.round(editForm.stock_qty);
        editForm.min_stock = Math.round(editForm.min_stock);
        editStockQtyDisplay.value = String(editForm.stock_qty);
        editMinStockDisplay.value = String(editForm.min_stock);
    }
});

// Filtered products based on search and category
const filteredProducts = computed(() => {
    return props.products.filter(p => {
        const matchesSearch = !search.value || 
            p.name.toLowerCase().includes(search.value.toLowerCase()) || 
            p.sku.toLowerCase().includes(search.value.toLowerCase());
        
        const matchesCategory = selectedCategory.value === null || 
            p.category_id === selectedCategory.value;

        return matchesSearch && matchesCategory;
    });
});

const sortedProducts = computed(() => {
    const getter = productSortOptions[productSortBy.value] || productSortOptions.created_at;

    return [...filteredProducts.value].sort((a, b) => {
        const valueA = getter(a);
        const valueB = getter(b);

        if (typeof valueA === 'number' && typeof valueB === 'number') {
            return productSortDesc.value ? valueB - valueA : valueA - valueB;
        }

        return productSortDesc.value
            ? String(valueB).localeCompare(String(valueA))
            : String(valueA).localeCompare(String(valueB));
    });
});

const paginatedProducts = computed(() => {
    const start = (productPage.value - 1) * itemsPerPage.value;
    return sortedProducts.value.slice(start, start + itemsPerPage.value);
});

const productPageCount = computed(() => {
    return Math.max(1, Math.ceil(sortedProducts.value.length / itemsPerPage.value));
});

const productRangeStart = computed(() => {
    if (sortedProducts.value.length === 0) return 0;
    return ((productPage.value - 1) * itemsPerPage.value) + 1;
});

const productRangeEnd = computed(() => {
    return Math.min(productPage.value * itemsPerPage.value, sortedProducts.value.length);
});

watch([search, selectedCategory, itemsPerPage], () => {
    productPage.value = 1;
});

watch(productPageCount, (count) => {
    if (productPage.value > count) {
        productPage.value = count;
    }
});

// Filtered stock movements based on search
const filteredStockMovements = computed(() => {
    if (!search.value) return props.stockMovements;
    const query = search.value.toLowerCase();
    return props.stockMovements.filter(m => {
        const stockId = ('IN-' + String(m.id).padStart(3, '0')).toLowerCase();
        const productName = m.product?.name?.toLowerCase() || '';
        const supplierName = m.supplier?.toLowerCase() || '';
        return stockId.includes(query) || productName.includes(query) || supplierName.includes(query);
    });
});

const sortedStockMovements = computed(() => {
    const getter = stockSortOptions[stockSortBy.value] || stockSortOptions.created_at;

    return [...(filteredStockMovements.value || [])].sort((a, b) => {
        const valueA = getter(a);
        const valueB = getter(b);

        if (typeof valueA === 'number' && typeof valueB === 'number') {
            return stockSortDesc.value ? valueB - valueA : valueA - valueB;
        }

        return stockSortDesc.value
            ? String(valueB).localeCompare(String(valueA))
            : String(valueA).localeCompare(String(valueB));
    });
});

const paginatedStockMovements = computed(() => {
    const start = (stockPage.value - 1) * stockItemsPerPage.value;
    return sortedStockMovements.value.slice(start, start + stockItemsPerPage.value);
});

const stockPageCount = computed(() => {
    return Math.max(1, Math.ceil(sortedStockMovements.value.length / stockItemsPerPage.value));
});

const stockRangeStart = computed(() => {
    if (sortedStockMovements.value.length === 0) return 0;
    return ((stockPage.value - 1) * stockItemsPerPage.value) + 1;
});

const stockRangeEnd = computed(() => {
    return Math.min(stockPage.value * stockItemsPerPage.value, sortedStockMovements.value.length);
});

watch([search, stockItemsPerPage], () => {
    stockPage.value = 1;
});

watch(stockPageCount, (count) => {
    if (stockPage.value > count) {
        stockPage.value = count;
    }
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

// Sanitize decimal input (allows digits, converts dot to comma, max one comma)
const sanitizeDecimalInput = (val) => {
    let cleaned = val.replace(/\./g, ',');
    cleaned = cleaned.replace(/[^0-9,]/g, '');
    const firstCommaIndex = cleaned.indexOf(',');
    if (firstCommaIndex !== -1) {
        const before = cleaned.slice(0, firstCommaIndex + 1);
        const after = cleaned.slice(firstCommaIndex + 1).replace(/,/g, '');
        cleaned = before + after;
    }
    return cleaned;
};

// Sanitize integer input (allows only digits)
const sanitizeIntegerInput = (val) => {
    return val.replace(/[^0-9]/g, '');
};

// Handle input for buy_price
const onBuyPriceInput = (e) => {
    const cleaned = sanitizeIntegerInput(e.target.value);
    const raw = parseInt(cleaned, 10) || 0;
    productForm.buy_price = raw;
    const formatted = formatNumber(raw);
    buyPriceDisplay.value = formatted;
    e.target.value = formatted;
};

// Handle input for sell_price
const onSellPriceInput = (e) => {
    const cleaned = sanitizeIntegerInput(e.target.value);
    const raw = parseInt(cleaned, 10) || 0;
    productForm.sell_price = raw;
    const formatted = formatNumber(raw);
    sellPriceDisplay.value = formatted;
    e.target.value = formatted;
};

// Handle input for total_cost
const onTotalCostInput = (e) => {
    const cleaned = sanitizeIntegerInput(e.target.value);
    const raw = parseInt(cleaned, 10) || 0;
    stockForm.total_cost = raw;
    const formatted = formatNumber(raw);
    totalCostDisplay.value = formatted;
    e.target.value = formatted;
};

// Handle input for edit buy_price
const onEditBuyPriceInput = (e) => {
    const cleaned = sanitizeIntegerInput(e.target.value);
    const raw = parseInt(cleaned, 10) || 0;
    editForm.buy_price = raw;
    const formatted = formatNumber(raw);
    editBuyPriceDisplay.value = formatted;
    e.target.value = formatted;
};

// Handle input for edit sell_price
const onEditSellPriceInput = (e) => {
    const cleaned = sanitizeIntegerInput(e.target.value);
    const raw = parseInt(cleaned, 10) || 0;
    editForm.sell_price = raw;
    const formatted = formatNumber(raw);
    editSellPriceDisplay.value = formatted;
    e.target.value = formatted;
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

// Format stock quantity helper
const formatStockQty = (qty, unit) => {
    const num = parseFloat(qty);
    if (isNaN(num)) return '0';
    const lowerUnit = String(unit || 'pcs').toLowerCase();
    if (lowerUnit === 'kg' || lowerUnit === 'liter' || lowerUnit === 'l') {
        if (num % 1 !== 0) {
            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 }).format(num);
        }
        return num.toString();
    }
    // For pcs or other units, round to nearest integer
    return Math.round(num).toString();
};

// Blur handlers to auto-multiply prices by 1000 if user types a small number like 12
const onBuyPriceBlur = () => {
    let val = productForm.buy_price;
    if (val > 0 && val < 1000) {
        val = val * 1000;
        productForm.buy_price = val;
        buyPriceDisplay.value = formatNumber(val);
    }
};

const onSellPriceBlur = () => {
    let val = productForm.sell_price;
    if (val > 0 && val < 1000) {
        val = val * 1000;
        productForm.sell_price = val;
        sellPriceDisplay.value = formatNumber(val);
    }
};

const onEditBuyPriceBlur = () => {
    let val = editForm.buy_price;
    if (val > 0 && val < 1000) {
        val = val * 1000;
        editForm.buy_price = val;
        editBuyPriceDisplay.value = formatNumber(val);
    }
};

const onEditSellPriceBlur = () => {
    let val = editForm.sell_price;
    if (val > 0 && val < 1000) {
        val = val * 1000;
        editForm.sell_price = val;
        editSellPriceDisplay.value = formatNumber(val);
    }
};

const onTotalCostBlur = () => {
    let val = stockForm.total_cost;
    if (val > 0 && val < 1000) {
        val = val * 1000;
        stockForm.total_cost = val;
        totalCostDisplay.value = formatNumber(val);
    }
};

// Handle input for edit min_stock
const onEditMinStockInput = (e) => {
    const unit = editForm.unit || 'pcs';
    const lowerUnit = String(unit).toLowerCase();
    const isDecimalAllowed = lowerUnit === 'kg' || lowerUnit === 'liter' || lowerUnit === 'l';
    
    const cleaned = isDecimalAllowed ? sanitizeDecimalInput(e.target.value) : sanitizeIntegerInput(e.target.value);
    e.target.value = cleaned;
    const raw = isDecimalAllowed ? parseNumberDecimal(cleaned) : (parseInt(cleaned, 10) || 0);
    editForm.min_stock = raw;
    editMinStockDisplay.value = cleaned;
};

// Handle input for min_stock (Add Product)
const onMinStockInput = (e) => {
    const unit = productForm.unit || 'pcs';
    const lowerUnit = String(unit).toLowerCase();
    const isDecimalAllowed = lowerUnit === 'kg' || lowerUnit === 'liter' || lowerUnit === 'l';
    
    const cleaned = isDecimalAllowed ? sanitizeDecimalInput(e.target.value) : sanitizeIntegerInput(e.target.value);
    e.target.value = cleaned;
    const raw = isDecimalAllowed ? parseNumberDecimal(cleaned) : (parseInt(cleaned, 10) || 0);
    productForm.min_stock = raw;
    minStockDisplay.value = cleaned;
};

// Handle input for stock_qty (Add Product)
const onStockQtyInput = (e) => {
    const unit = productForm.unit || 'pcs';
    const lowerUnit = String(unit).toLowerCase();
    const isDecimalAllowed = lowerUnit === 'kg' || lowerUnit === 'liter' || lowerUnit === 'l';
    
    const cleaned = isDecimalAllowed ? sanitizeDecimalInput(e.target.value) : sanitizeIntegerInput(e.target.value);
    e.target.value = cleaned;
    const raw = isDecimalAllowed ? parseNumberDecimal(cleaned) : (parseInt(cleaned, 10) || 0);
    productForm.stock_qty = raw;
    stockQtyDisplay.value = cleaned;
};

// Handle input for qty (Add Stock)
const onQtyInput = (e) => {
    const product = props.products.find(p => p.id === stockForm.product_id);
    const unit = product?.unit || 'pcs';
    const lowerUnit = String(unit).toLowerCase();
    const isDecimalAllowed = lowerUnit === 'kg' || lowerUnit === 'liter' || lowerUnit === 'l';
    
    const cleaned = isDecimalAllowed ? sanitizeDecimalInput(e.target.value) : sanitizeIntegerInput(e.target.value);
    e.target.value = cleaned;
    const raw = isDecimalAllowed ? parseNumberDecimal(cleaned) : (parseInt(cleaned, 10) || 0);
    stockForm.qty = raw;
    qtyDisplay.value = cleaned;
};

// Handle input for edit stock_qty
const onEditStockQtyInput = (e) => {
    const unit = editForm.unit || 'pcs';
    const lowerUnit = String(unit).toLowerCase();
    const isDecimalAllowed = lowerUnit === 'kg' || lowerUnit === 'liter' || lowerUnit === 'l';
    
    const cleaned = isDecimalAllowed ? sanitizeDecimalInput(e.target.value) : sanitizeIntegerInput(e.target.value);
    e.target.value = cleaned;
    const raw = isDecimalAllowed ? parseNumberDecimal(cleaned) : (parseInt(cleaned, 10) || 0);
    editForm.stock_qty = raw;
    editStockQtyDisplay.value = cleaned;
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
    editForm.image = null;
    editImagePreview.value = product.image_url || null;
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

const escapeCSV = (value) => {
    if (value === null || value === undefined) return '""';
    return `"${String(value).replace(/"/g, '""')}"`;
};

const downloadCSV = (filename, headers, rows) => {
    const headerLine = headers.map(escapeCSV).join(';');
    const contentLines = rows.map(row => row.map(escapeCSV).join(';'));
    const csvContent = "\uFEFF" + "sep=;\n" + [headerLine, ...contentLines].join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    const date = new Date().toISOString().split('T')[0];

    link.href = url;
    link.download = `${filename}_${date}.csv`;
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
};

const exportProducts = () => {
    const rows = sortedProducts.value.map(product => [
        product.sku,
        product.name,
        product.category?.name || '-',
        formatStockQty(product.stock_qty, product.unit),
        product.unit || 'pcs',
        formatStockQty(product.min_stock, product.unit),
        product.buy_price,
        product.sell_price,
        getStatusLabel(product),
    ]);

    downloadCSV(
        'grocerypro_products',
        ['Product ID', 'Name', 'Category', 'Stock', 'Unit', 'Minimum Stock', 'Buy Price', 'Sell Price', 'Status'],
        rows
    );
    showSnackbar(`Exported ${rows.length} product${rows.length !== 1 ? 's' : ''}.`);
};

const exportIncomingStock = () => {
    const rows = (sortedStockMovements.value || []).map(movement => [
        formatStockId(movement.id),
        formatDate(movement.created_at),
        movement.product?.name || '-',
        formatStockQty(movement.qty, movement.product?.unit),
        movement.product?.unit || 'pcs',
        movement.supplier || '-',
        movement.total_cost || 0,
    ]);

    downloadCSV(
        'grocerypro_incoming_stock',
        ['Stock ID', 'Date', 'Product Name', 'Quantity', 'Unit', 'Supplier', 'Total Cost'],
        rows
    );
    showSnackbar(`Exported ${rows.length} incoming stock record${rows.length !== 1 ? 's' : ''}.`);
};

const handleExport = () => {
    if (tab.value === 'incoming_stock') {
        exportIncomingStock();
        return;
    }

    exportProducts();
};

const submitProduct = () => {
    // Auto multiply by 1000 if less than 1000 (e.g. 12 becomes 12000)
    if (productForm.buy_price > 0 && productForm.buy_price < 1000) {
        productForm.buy_price *= 1000;
    }
    if (productForm.sell_price > 0 && productForm.sell_price < 1000) {
        productForm.sell_price *= 1000;
    }
    // Auto round stock quantities if unit is not kg/liter/l
    const unit = productForm.unit || 'pcs';
    const lowerUnit = String(unit).toLowerCase();
    if (lowerUnit !== 'kg' && lowerUnit !== 'liter' && lowerUnit !== 'l') {
        productForm.stock_qty = Math.round(productForm.stock_qty);
        productForm.min_stock = Math.round(productForm.min_stock);
    }

    if (productForm.stock_qty <= 0 || productForm.min_stock <= 0 || productForm.buy_price <= 0 || productForm.sell_price <= 0) {
        showSnackbar('Stock and pricing values must be greater than 0.', 'error');
        return;
    }
    const productName = productForm.name;
    productForm.post(route('products.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            addProductDialog.value = false;
            productForm.reset();
            buyPriceDisplay.value = '0';
            sellPriceDisplay.value = '0';
            stockQtyDisplay.value = '0';
            minStockDisplay.value = '0';
            resetProductImage();
            validationErrors.value.productName = '';
            validationErrors.value.productSupplier = '';
            showSnackbar(`${productName} added successfully.`);
        },
        onError: () => {
            showSnackbar('Failed to add product. Please check the form fields.', 'error');
        }
    });
};

const submitStock = () => {
    // Auto multiply by 1000 if less than 1000
    if (stockForm.total_cost > 0 && stockForm.total_cost < 1000) {
        stockForm.total_cost *= 1000;
    }
    // Auto round quantity if selected product unit is not kg/liter/l
    const product = props.products.find(p => p.id === stockForm.product_id);
    const unit = product?.unit || 'pcs';
    const lowerUnit = String(unit).toLowerCase();
    if (lowerUnit !== 'kg' && lowerUnit !== 'liter' && lowerUnit !== 'l') {
        stockForm.qty = Math.round(stockForm.qty);
    }

    if (stockForm.qty <= 0 || stockForm.total_cost <= 0) {
        showSnackbar('Quantity and total cost must be greater than 0.', 'error');
        return;
    }
    const stockProductName = product?.name || 'Selected product';
    stockForm.post(route('products.add-stock'), {
        preserveScroll: true,
        onSuccess: () => {
            addStockDialog.value = false;
            stockForm.reset();
            stockForm.date_received = new Date().toISOString().split('T')[0];
            totalCostDisplay.value = '0';
            qtyDisplay.value = '0';
            validationErrors.value.stockSupplier = '';
            showSnackbar(`Incoming stock added for ${stockProductName}.`);
        },
        onError: () => {
            showSnackbar('Failed to add stock. Please check the form fields.', 'error');
        }
    });
};

const submitEditProduct = () => {
    // Auto multiply by 1000 if less than 1000 (e.g. 12 becomes 12000)
    if (editForm.buy_price > 0 && editForm.buy_price < 1000) {
        editForm.buy_price *= 1000;
    }
    if (editForm.sell_price > 0 && editForm.sell_price < 1000) {
        editForm.sell_price *= 1000;
    }
    // Auto round stock quantities if unit is not kg/liter/l
    const unit = editForm.unit || 'pcs';
    const lowerUnit = String(unit).toLowerCase();
    if (lowerUnit !== 'kg' && lowerUnit !== 'liter' && lowerUnit !== 'l') {
        editForm.stock_qty = Math.round(editForm.stock_qty);
        editForm.min_stock = Math.round(editForm.min_stock);
    }

    if (editForm.stock_qty <= 0 || editForm.min_stock <= 0 || editForm.buy_price <= 0 || editForm.sell_price <= 0) {
        showSnackbar('Stock and pricing values must be greater than 0.', 'error');
        return;
    }
    const editedProductName = editForm.name;
    editForm.post(route('products.update', editingProduct.value.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            editProductDialog.value = false;
            editForm.reset();
            editBuyPriceDisplay.value = '0';
            editSellPriceDisplay.value = '0';
            editMinStockDisplay.value = '0';
            editStockQtyDisplay.value = '0';
            resetEditImage();
            editingProduct.value = null;
            validationErrors.value.editName = '';
            showSnackbar(`${editedProductName} updated successfully.`);
        },
        onError: () => {
            showSnackbar('Failed to update product. Please check the form fields.', 'error');
        }
    });
};

// Open delete confirmation dialog
const openDeleteDialog = (product) => {
    deletingProduct.value = product;
    deleteDialog.value = true;
};

// Delete product
const deleteProduct = () => {
    if (!deletingProduct.value) return;
    const deletedProductName = deletingProduct.value.name;
    
    router.delete(route('products.destroy', deletingProduct.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleteDialog.value = false;
            deletingProduct.value = null;
            showSnackbar(`${deletedProductName} deleted successfully.`);
        },
        onError: (errors) => {
            deleteDialog.value = false;
            deletingProduct.value = null;
            showSnackbar(errors.delete || 'Failed to delete product.', 'error');
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
            <v-btn variant="outlined" color="grey-darken-2" rounded="lg" class="text-none mr-2" height="40" @click="handleExport">
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
                        :placeholder="tab === 'products' ? 'Search products...' : 'Search stock history...'"
                        variant="outlined"
                        density="compact"
                        hide-details
                        rounded="lg"
                    ></v-text-field>
                </v-col>
                <v-spacer></v-spacer>
                <v-col cols="auto">
                    <v-btn 
                        v-if="tab === 'incoming_stock'"
                        color="primary" 
                        rounded="lg" 
                        class="text-none mr-2"
                        @click="addStockDialog = true"
                    >
                        <v-icon start size="small">mdi-package-variant-plus</v-icon>
                        Incoming Stock
                    </v-btn>
                    <v-btn 
                        v-if="tab === 'products'"
                        color="primary" 
                        rounded="lg" 
                        class="text-none"
                        @click="openAddProductDialog"
                    >
                        <v-icon start size="small">mdi-plus</v-icon>
                        Add Product
                    </v-btn>
                </v-col>
            </v-row>

            <!-- Tabs -->
            <v-tabs
                v-model="tab"
                color="primary"
                align-tabs="start"
                class="mb-6"
                density="compact"
            >
                <v-tab value="products" :ripple="true">Products</v-tab>
                <v-tab value="incoming_stock" :ripple="true">Incoming Stock</v-tab>
            </v-tabs>
            <div v-if="tab === 'products'" class="mb-3">
                <div class="product-filter-row d-flex align-center ga-3">
                    <v-chip-group v-model="selectedCategory" class="category-chip-scroller" mandatory>
                        <v-chip :value="null" filter variant="outlined" color="primary" class="border text-grey">All</v-chip>
                        <v-chip
                            v-for="cat in categories"
                            :key="cat.id"
                            :value="cat.id"
                            filter
                            variant="outlined"
                            color="primary"
                            class="border text-grey"
                        >
                            {{ cat.name }}
                        </v-chip>
                    </v-chip-group>
                </div>
            </div>
            <v-window v-model="tab" class="overflow-visible">
                <v-window-item value="products">
                    <!-- Products Table -->
                    <v-card class="rounded-xl border" elevation="0">
                        <v-table hover>
                    <thead>
                        <tr>
                            <th class="text-left font-weight-bold text-grey-darken-2 sortable-heading" @click="sortProducts('sku')">Product ID <v-icon size="14">{{ getSortIcon('sku') }}</v-icon></th>
                            <th class="text-left font-weight-bold text-grey-darken-2">Image</th>
                            <th class="text-left font-weight-bold text-grey-darken-2 sortable-heading" @click="sortProducts('name')">Name <v-icon size="14">{{ getSortIcon('name') }}</v-icon></th>
                            <th class="text-left font-weight-bold text-grey-darken-2 sortable-heading" @click="sortProducts('category')">Category <v-icon size="14">{{ getSortIcon('category') }}</v-icon></th>
                            <th class="text-left font-weight-bold text-grey-darken-2 sortable-heading" @click="sortProducts('stock_qty')">Stock <v-icon size="14">{{ getSortIcon('stock_qty') }}</v-icon></th>
                            <th class="text-left font-weight-bold text-grey-darken-2 sortable-heading" @click="sortProducts('min_stock')">Minimum Stock <v-icon size="14">{{ getSortIcon('min_stock') }}</v-icon></th>
                            <th class="text-left font-weight-bold text-grey-darken-2 sortable-heading" @click="sortProducts('buy_price')">Buy Price <v-icon size="14">{{ getSortIcon('buy_price') }}</v-icon></th>
                            <th class="text-left font-weight-bold text-grey-darken-2 sortable-heading" @click="sortProducts('sell_price')">Sell Price <v-icon size="14">{{ getSortIcon('sell_price') }}</v-icon></th>
                            <th class="text-left font-weight-bold text-grey-darken-2 sortable-heading" @click="sortProducts('status')">Status <v-icon size="14">{{ getSortIcon('status') }}</v-icon></th>
                            <th class="text-center font-weight-bold text-grey-darken-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in paginatedProducts" :key="product.id">
                            <td class="font-weight-medium">{{ product.sku }}</td>
                            <td>
                                <div class="product-table-thumb">
                                    <v-img
                                        v-if="product.image_url"
                                        :src="product.image_url"
                                        cover
                                        width="100%"
                                        height="100%"
                                    ></v-img>
                                    <v-icon v-else size="20" color="grey">mdi-image-outline</v-icon>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-center">
                                    <v-icon size="small" color="primary" class="mr-2">mdi-package-variant</v-icon>
                                    {{ product.name }}
                                </div>
                            </td>
                            <td class="text-grey-darken-1">{{ product.category?.name || '-' }}</td>
                            <td>{{ formatStockQty(product.stock_qty, product.unit) }} {{ product.unit || 'pcs' }}</td>
                            <td>{{ formatStockQty(product.min_stock, product.unit) }} {{ product.unit || 'pcs' }}</td>
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
                                    <v-btn icon size="small" variant="text" color="error" @click="openDeleteDialog(product)">
                                        <v-icon size="18">mdi-delete-outline</v-icon>
                                    </v-btn>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredProducts.length === 0">
                            <td colspan="10" class="text-center py-8 text-grey">
                                No products found.
                            </td>
                        </tr>
                    </tbody>
                </v-table>
                <div class="d-flex align-center justify-space-between flex-wrap ga-3 px-4 py-3 border-t">
                    <div class="text-caption text-grey-darken-1">
                        Showing {{ productRangeStart }}-{{ productRangeEnd }} of {{ sortedProducts.length }} products
                    </div>
                    <div class="d-flex align-center ga-3">
                        <v-select
                            v-model="itemsPerPage"
                            :items="[10, 25, 50]"
                            density="compact"
                            hide-details
                            variant="outlined"
                            style="width: 92px;"
                        ></v-select>
                        <v-pagination
                            v-model="productPage"
                            :length="productPageCount"
                            density="comfortable"
                            size="small"
                            total-visible="5"
                        ></v-pagination>
                    </div>
                </div>
                    </v-card>
                </v-window-item>

                <!-- Incoming Stock History -->
                <v-window-item value="incoming_stock">
                    <v-card class="rounded-xl border" elevation="0">
                <v-table hover>
                    <thead>
                        <tr>
                            <th class="text-left font-weight-bold text-grey-darken-2 sortable-heading" @click="sortStockMovements('id')">Stock ID <v-icon size="14">{{ getStockSortIcon('id') }}</v-icon></th>
                            <th class="text-left font-weight-bold text-grey-darken-2 sortable-heading" @click="sortStockMovements('created_at')">Date <v-icon size="14">{{ getStockSortIcon('created_at') }}</v-icon></th>
                            <th class="text-left font-weight-bold text-grey-darken-2 sortable-heading" @click="sortStockMovements('product_name')">Product Name <v-icon size="14">{{ getStockSortIcon('product_name') }}</v-icon></th>
                            <th class="text-left font-weight-bold text-grey-darken-2 sortable-heading" @click="sortStockMovements('qty')">Quantity <v-icon size="14">{{ getStockSortIcon('qty') }}</v-icon></th>
                            <th class="text-left font-weight-bold text-grey-darken-2 sortable-heading" @click="sortStockMovements('supplier')">Supplier <v-icon size="14">{{ getStockSortIcon('supplier') }}</v-icon></th>
                            <th class="text-left font-weight-bold text-grey-darken-2 sortable-heading" @click="sortStockMovements('total_cost')">Total Cost <v-icon size="14">{{ getStockSortIcon('total_cost') }}</v-icon></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="movement in paginatedStockMovements" :key="movement.id">
                            <td class="font-weight-medium">{{ formatStockId(movement.id) }}</td>
                            <td>{{ formatDate(movement.created_at) }}</td>
                            <td>
                                <div class="d-flex align-center">
                                    <v-icon size="small" color="primary" class="mr-2">mdi-package-variant</v-icon>
                                    {{ movement.product?.name || '-' }}
                                </div>
                            </td>
                            <td class="text-success font-weight-medium">+{{ formatStockQty(movement.qty, movement.product?.unit) }} {{ movement.product?.unit || 'pcs' }}</td>
                            <td>{{ movement.supplier || '-' }}</td>
                            <td class="font-weight-medium">{{ formatPrice(movement.total_cost || 0) }}</td>
                        </tr>
                        <tr v-if="!filteredStockMovements || filteredStockMovements.length === 0">
                            <td colspan="6" class="text-center py-8 text-grey">
                                No incoming stock history found.
                            </td>
                        </tr>
                    </tbody>
                </v-table>
                <div class="d-flex align-center justify-space-between flex-wrap ga-3 px-4 py-3 border-t">
                    <div class="text-caption text-grey-darken-1">
                        Showing {{ stockRangeStart }}-{{ stockRangeEnd }} of {{ sortedStockMovements.length }} records
                    </div>
                    <div class="d-flex align-center ga-3">
                        <v-select
                            v-model="stockItemsPerPage"
                            :items="[10, 25, 50]"
                            density="compact"
                            hide-details
                            variant="outlined"
                            style="width: 92px;"
                        ></v-select>
                        <v-pagination
                            v-model="stockPage"
                            :length="stockPageCount"
                            density="comfortable"
                            size="small"
                            total-visible="5"
                        ></v-pagination>
                    </div>
                </div>
                    </v-card>
                </v-window-item>
            </v-window>
        </v-container>

        <!-- Add Product Dialog -->
        <v-dialog v-model="addProductDialog" max-width="600" persistent>
            <v-card class="rounded-xl">
                <v-card-title class="pa-4 font-weight-bold d-flex justify-space-between align-center border-b">
                    Add New Product
                    <v-btn icon="mdi-close" variant="text" size="small" @click="closeAddProductDialog"></v-btn>
                </v-card-title>
                
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
                            ></v-select>
                        </v-col>
                    </v-row>

                    <v-divider class="my-4"></v-divider>

                    <!-- Product Image Section -->
                    <div class="text-subtitle-2 font-weight-bold text-grey-darken-2 mb-3">
                        <v-icon size="small" class="mr-1">mdi-image-outline</v-icon>
                        Product Image
                    </div>
                    <v-row class="mb-2" align="center">
                        <v-col cols="12" sm="4">
                            <div class="product-image-preview">
                                <v-img
                                    v-if="productImagePreview"
                                    :src="productImagePreview"
                                    cover
                                    width="100%"
                                    height="100%"
                                ></v-img>
                                <div v-else class="product-image-placeholder">
                                    <v-icon size="32" color="grey">mdi-image-plus-outline</v-icon>
                                    <span>No image</span>
                                </div>
                            </div>
                        </v-col>
                        <v-col cols="12" sm="8">
                            <v-file-input
                                :model-value="productForm.image"
                                @update:model-value="setProductImage"
                                accept="image/png,image/jpeg,image/jpg,image/webp"
                                variant="outlined"
                                density="comfortable"
                                label="Upload product image"
                                prepend-icon=""
                                prepend-inner-icon="mdi-upload"
                                :error-messages="productForm.errors.image"
                                rounded="lg"
                                show-size
                                clearable
                            ></v-file-input>
                            <div class="text-caption text-grey-darken-1">
                                PNG, JPG, JPEG, or WEBP. Max 2 MB.
                            </div>
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
                                :rules="greaterThanZeroRule"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                :error-messages="productForm.errors.stock_qty"
                                rounded="lg"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="4">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Min Stock</div>
                            <v-text-field
                                :model-value="minStockDisplay"
                                @input="onMinStockInput"
                                :rules="greaterThanZeroRule"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                :error-messages="productForm.errors.min_stock"
                                rounded="lg"
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
                                @blur="onBuyPriceBlur"
                                :rules="priceGreaterThanZeroRule"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                prefix="Rp"
                                :error-messages="productForm.errors.buy_price"
                                rounded="lg"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Sell Price</div>
                            <v-text-field
                                :model-value="sellPriceDisplay"
                                @input="onSellPriceInput"
                                @blur="onSellPriceBlur"
                                :rules="priceGreaterThanZeroRule"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                prefix="Rp"
                                :error-messages="productForm.errors.sell_price"
                                rounded="lg"
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
                            ></v-text-field>
                        </v-col>
                    </v-row>
                </v-card-text>
                
                <v-card-actions class="pa-4 pt-4 border-t mt-4">
                    <v-spacer></v-spacer>
                    <v-btn
                        variant="tonal"
                        @click="closeAddProductDialog"
                        rounded="lg"
                        class="px-4 text-none"
                    >
                        Cancel
                    </v-btn>
                    <v-btn
                        color="primary"
                        @click="submitProduct"
                        :loading="productForm.processing"
                        :disabled="!!validationErrors.productName || !!validationErrors.productSupplier || productForm.stock_qty <= 0 || productForm.min_stock <= 0 || productForm.buy_price <= 0 || productForm.sell_price <= 0"
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
                <v-card-title class="pa-4 font-weight-bold d-flex justify-space-between align-center border-b">
                    Edit Product
                    <v-btn icon="mdi-close" variant="text" size="small" @click="closeEditProductDialog"></v-btn>
                </v-card-title>
                
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
                            ></v-select>
                        </v-col>
                    </v-row>

                    <v-divider class="my-4"></v-divider>

                    <!-- Product Image Section -->
                    <div class="text-subtitle-2 font-weight-bold text-grey-darken-2 mb-3">
                        <v-icon size="small" class="mr-1">mdi-image-outline</v-icon>
                        Product Image
                    </div>
                    <v-row class="mb-2" align="center">
                        <v-col cols="12" sm="4">
                            <div class="product-image-preview">
                                <v-img
                                    v-if="editImagePreview"
                                    :src="editImagePreview"
                                    cover
                                    width="100%"
                                    height="100%"
                                ></v-img>
                                <div v-else class="product-image-placeholder">
                                    <v-icon size="32" color="grey">mdi-image-plus-outline</v-icon>
                                    <span>No image</span>
                                </div>
                            </div>
                        </v-col>
                        <v-col cols="12" sm="8">
                            <v-file-input
                                :model-value="editForm.image"
                                @update:model-value="setEditImage"
                                accept="image/png,image/jpeg,image/jpg,image/webp"
                                variant="outlined"
                                density="comfortable"
                                label="Replace product image"
                                prepend-icon=""
                                prepend-inner-icon="mdi-upload"
                                :error-messages="editForm.errors.image"
                                rounded="lg"
                                show-size
                                clearable
                            ></v-file-input>
                            <div class="text-caption text-grey-darken-1">
                                Leave empty to keep the current image.
                            </div>
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
                                :rules="greaterThanZeroRule"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                :error-messages="editForm.errors.stock_qty"
                                rounded="lg"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="4">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Min Stock</div>
                            <v-text-field
                                :model-value="editMinStockDisplay"
                                @input="onEditMinStockInput"
                                :rules="greaterThanZeroRule"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                :error-messages="editForm.errors.min_stock"
                                rounded="lg"
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
                                @blur="onEditBuyPriceBlur"
                                :rules="priceGreaterThanZeroRule"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                prefix="Rp"
                                :error-messages="editForm.errors.buy_price"
                                rounded="lg"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Sell Price</div>
                            <v-text-field
                                :model-value="editSellPriceDisplay"
                                @input="onEditSellPriceInput"
                                @blur="onEditSellPriceBlur"
                                :rules="priceGreaterThanZeroRule"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                prefix="Rp"
                                :error-messages="editForm.errors.sell_price"
                                rounded="lg"
                            ></v-text-field>
                        </v-col>
                    </v-row>
                </v-card-text>
                
                <v-card-actions class="pa-4 pt-4 border-t mt-4">
                    <v-spacer></v-spacer>
                    <v-btn
                        variant="tonal"
                        @click="closeEditProductDialog"
                        rounded="lg"
                        class="px-4 text-none"
                    >
                        Cancel
                    </v-btn>
                    <v-btn
                        color="primary"
                        @click="submitEditProduct"
                        :loading="editForm.processing"
                        :disabled="!!validationErrors.editName || editForm.stock_qty <= 0 || editForm.min_stock <= 0 || editForm.buy_price <= 0 || editForm.sell_price <= 0"
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
                <v-card-title class="pa-4 font-weight-bold d-flex justify-space-between align-center border-b">
                    Add Incoming Stock
                    <v-btn icon="mdi-close" variant="text" size="small" @click="addStockDialog = false"></v-btn>
                </v-card-title>
                
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
                                :rules="greaterThanZeroRule"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                :error-messages="stockForm.errors.qty"
                                rounded="lg"
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
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                            <div class="mb-2 text-subtitle-2 font-weight-medium">Total Cost</div>
                            <v-text-field
                                :model-value="totalCostDisplay"
                                @input="onTotalCostInput"
                                @blur="onTotalCostBlur"
                                :rules="priceGreaterThanZeroRule"
                                variant="outlined"
                                density="comfortable"
                                placeholder="0"
                                prefix="Rp"
                                :error-messages="stockForm.errors.total_cost"
                                rounded="lg"
                            ></v-text-field>
                        </v-col>
                    </v-row>
                </v-card-text>
                
                <v-card-actions class="pa-4 pt-4 border-t mt-4">
                    <v-spacer></v-spacer>
                    <v-btn
                        variant="tonal"
                        @click="addStockDialog = false"
                        rounded="lg"
                        class="px-4 text-none"
                    >
                        Cancel
                    </v-btn>
                    <v-btn
                        color="primary"
                        @click="submitStock"
                        :loading="stockForm.processing"
                        :disabled="!!validationErrors.stockSupplier || stockForm.qty <= 0 || stockForm.total_cost <= 0"
                        rounded="lg"
                        class="px-4 text-none"
                        variant="flat"
                    >
                        Add Stock
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Delete Confirmation Dialog -->
        <v-dialog v-model="deleteDialog" max-width="400" persistent>
            <v-card class="rounded-xl">
                <v-card-title class="pa-4 font-weight-bold d-flex align-center">
                    <v-icon color="error" class="mr-2">mdi-alert-circle</v-icon>
                    Delete Product
                </v-card-title>
                
                <v-card-text class="pa-4">
                    <p class="text-body-1 mb-2">Are you sure you want to delete this product?</p>
                    <v-alert
                        v-if="deletingProduct"
                        type="warning"
                        variant="tonal"
                        density="compact"
                        class="mb-0"
                    >
                        <strong>{{ deletingProduct?.name }}</strong> ({{ deletingProduct?.sku }})
                    </v-alert>
                    <p class="text-body-2 text-grey mt-3 mb-0">
                        This action cannot be undone. All related stock movements will also be deleted.
                    </p>
                </v-card-text>
                
                <v-card-actions class="pa-4 pt-0">
                    <v-spacer></v-spacer>
                    <v-btn
                        variant="text"
                        color="grey-darken-1"
                        @click="deleteDialog = false; deletingProduct = null"
                        rounded="lg"
                        class="px-4 text-none"
                    >
                        Cancel
                    </v-btn>
                    <v-btn
                        color="error"
                        @click="deleteProduct"
                        rounded="lg"
                        class="px-4 text-none"
                        variant="flat"
                    >
                        <v-icon start size="small">mdi-delete</v-icon>
                        Delete
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Snackbar -->
        <v-snackbar v-model="snackbar" :timeout="3000" :color="snackbarColor" location="bottom right">
            {{ snackbarMessage }}
        </v-snackbar>

    </AuthenticatedLayout>
</template>

<style scoped>
.sortable-heading {
    cursor: pointer;
    user-select: none;
    white-space: nowrap;
}

.sortable-heading:hover {
    color: rgb(var(--v-theme-primary)) !important;
}

.product-filter-row {
    max-width: 100%;
    overflow: hidden;
}

.category-chip-scroller {
    min-width: 0;
    max-width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    white-space: nowrap;
}

.category-chip-scroller :deep(.v-slide-group__container) {
    overflow-x: auto;
    scrollbar-width: thin;
}

.category-chip-scroller :deep(.v-slide-group__content) {
    flex-wrap: nowrap;
    width: max-content;
}

.category-chip-scroller :deep(.v-chip) {
    flex: 0 0 auto;
}

.product-image-preview {
    width: 100%;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    border: 1px solid rgba(var(--v-border-color), 0.2);
    border-radius: 16px;
    background: rgba(var(--v-theme-primary), 0.045);
}

.product-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    color: rgba(var(--v-theme-on-surface), 0.56);
    font-size: 0.78rem;
}

.product-table-thumb {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 1px solid rgba(var(--v-border-color), 0.18);
    border-radius: 12px;
    background: rgba(var(--v-theme-primary), 0.045);
}
</style>

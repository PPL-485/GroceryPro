<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useTheme } from 'vuetify';
import axios from 'axios';

const props = defineProps({
    categories: Array,
    products: Array,
});

const theme = useTheme()

const search = ref('');
const searchInput = ref(null);
const selectedCategory = ref(null);
const localProducts = ref([...props.products]);
const cart = ref([]);
const paymentMethod = ref('cash');
const amountPaid = ref(0);
const snackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');
const isQrisFinalizing = ref(false);
const qrisStatusMessage = ref('');
const clearCartDialog = ref(false);

// Computed properties
const filteredProducts = computed(() => {
    let filtered = localProducts.value;
    if (selectedCategory.value !== null) {
        filtered = filtered.filter(p => p.category_id === selectedCategory.value);
    }
    if (search.value) {
        const query = search.value.toLowerCase();
        filtered = filtered.filter(p => 
            p.name.toLowerCase().includes(query) || 
            (p.sku && p.sku.toLowerCase().includes(query))
        );
    }
    return filtered;
});

const subtotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + (item.unit_price * item.qty), 0);
});

const cartItemCount = computed(() => {
    return cart.value.length;
});

const tax = computed(() => {
    return Math.round(subtotal.value * 0.10); // 10% tax
});

const total = computed(() => {
    return Math.round(subtotal.value + tax.value);
});

const change = computed(() => {
    if (paymentMethod.value !== 'cash') {
        return 0;
    }
    return Math.max(amountPaid.value - total.value, 0);
});

watch(paymentMethod, (newVal) => {
    if (newVal === 'cash' && amountPaid.value === 0) {
        amountPaid.value = total.value;
    }
});

watch(() => props.products, (products) => {
    localProducts.value = [...products];
});

// Methods
const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
};

const getStockChipColor = (product) => {
    return Number(product.stock_qty) <= Number(product.min_stock) ? 'error' : 'primary';
};

const showSnackbar = (message, color = 'success') => {
    snackbarMessage.value = message;
    snackbarColor.value = color;
    snackbar.value = true;
};

const applyStockChanges = (items) => {
    items.forEach(item => {
        const product = localProducts.value.find(p => p.id === item.product_id);
        if (product) {
            product.stock_qty = Math.max(Number(product.stock_qty || 0) - Number(item.qty || 0), 0);
        }
    });
};

const clearCart = () => {
    cart.value = [];
    amountPaid.value = 0;
    clearCartDialog.value = false;
    showSnackbar('Cart cleared.', 'info');
};

const addToCart = (product) => {
    if (isCheckoutLocked.value) {
        return;
    }

    if (product.stock_qty <= 0) {
        showSnackbar('Product out of stock!', 'error');
        return;
    }
    const existing = cart.value.find(i => i.product_id === product.id);
    if (existing) {
        if (existing.qty < product.stock_qty) {
            existing.qty++;
            existing.subtotal = existing.qty * existing.unit_price;
            showSnackbar(`${product.name} quantity updated in cart.`);
        } else {
            showSnackbar('Cannot add more than available stock!', 'error');
        }
    } else {
        cart.value.push({
            product_id: product.id,
            name: product.name,
            unit_price: product.sell_price,
            qty: 1,
            subtotal: product.sell_price,
            stock_qty: product.stock_qty,
        });
        showSnackbar(`${product.name} added to cart.`);
    }
};

const updateQty = (item, delta) => {
    if (isCheckoutLocked.value) {
        return;
    }

    const newQty = item.qty + delta;
    if (newQty > 0 && newQty <= item.stock_qty) {
        item.qty = newQty;
        item.subtotal = item.qty * item.unit_price;
    } else if (newQty === 0) {
        cart.value = cart.value.filter(i => i.product_id !== item.product_id);
    }
};

const handleShortcut = (event) => {
    const target = event.target;
    const isTyping = ['INPUT', 'TEXTAREA'].includes(target?.tagName) || target?.isContentEditable;

    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        searchInput.value?.focus?.();
        return;
    }

    if (isTyping || isCheckoutLocked.value) {
        return;
    }

    if (event.key === 'F2') {
        event.preventDefault();
        paymentMethod.value = 'cash';
    } else if (event.key === 'F3') {
        event.preventDefault();
        paymentMethod.value = 'qris';
    } else if (event.key === 'Enter' && cart.value.length > 0) {
        event.preventDefault();
        checkout();
    } else if (event.key === 'Escape' && cart.value.length > 0) {
        event.preventDefault();
        clearCartDialog.value = true;
    }
};

const form = useForm({
    items: [],
    payment_method: 'cash',
    total_amount: 0,
    change: 0,
});

const isCheckoutLocked = computed(() => form.processing || isQrisFinalizing.value);

const checkout = async () => {
    if (isCheckoutLocked.value) {
        return;
    }

    if (cart.value.length === 0) {
        showSnackbar('Cart is empty!', 'error');
        return;
    }
    
    if (paymentMethod.value === 'cash' && amountPaid.value < total.value) {
        showSnackbar('Amount paid is less than total!', 'error');
        return;
    }

    form.items = cart.value.map(item => ({
        product_id: item.product_id,
        qty: item.qty,
        unit_price: item.unit_price,
        subtotal: item.subtotal
    }));
    form.payment_method = paymentMethod.value;
    form.total_amount = total.value; // Store the final total with tax
    form.change = change.value;

    if (paymentMethod.value === 'qris') {
        try {
            form.processing = true;
            const response = await axios.post(route('transactions.store'), form.data());
            const snapToken = response.data.snap_token;
            
            window.snap.pay(snapToken, {
                onSuccess: async function(result){
                    try {
                        isQrisFinalizing.value = true;
                        qrisStatusMessage.value = 'Confirming QRIS payment...';
                        await window.axios.post('/transactions/mark-paid', { order_id: response.data.trx_code });
                        qrisStatusMessage.value = 'Updating stock display...';
                        applyStockChanges(cart.value);
                        cart.value = [];
                        amountPaid.value = 0;
                        showSnackbar('QRIS payment successful.');
                        isQrisFinalizing.value = false;
                        qrisStatusMessage.value = '';
                    } catch (err) {
                        console.error(err);
                        isQrisFinalizing.value = false;
                        qrisStatusMessage.value = '';
                        showSnackbar('Failed to update system: ' + (err.response?.data?.message || err.message), 'error');
                    }
                },
                onPending: function(result){
                    showSnackbar('Waiting for your payment.', 'info');
                },
                onError: function(result){
                    showSnackbar('Payment failed!', 'error');
                },
                onClose: function(){
                    showSnackbar('You closed the payment popup. Transaction is pending.', 'warning');
                }
            });
        } catch (error) {
            console.error(error);
            showSnackbar(error.response?.data?.error || 'An error occurred during checkout.', 'error');
        } finally {
            form.processing = false;
        }
    } else {
        form.post(route('transactions.store'), {
            preserveScroll: true,
            onSuccess: () => {
                applyStockChanges(cart.value);
                cart.value = [];
                amountPaid.value = 0;
                showSnackbar('Transaction completed successfully.');
            },
            onError: (errors) => {
                console.error(errors);
                showSnackbar(errors.error || Object.values(errors)[0] || 'An error occurred during checkout.', 'error');
            }
        });
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleShortcut);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleShortcut);
});
</script>

<template>
    <Head title="Point of Sale" />

    <AuthenticatedLayout :transaction-cart-item-count="cartItemCount" :transaction-cart-subtotal="subtotal">
        <template #header-title>
            Point of Sale (POS)
        </template>

        <template #header-description>
            <p class="text-sm">
                Scan or search products to add to cart
            </p>
        </template>

        <!-- ── Column 3: Transactions Cart Sidebar ── -->
        <template #transactions-sidebar>
            <div class="d-flex flex-column h-100">
                <!-- Header -->
                <v-toolbar flat color="surface">
                    <template #prepend>
                        <v-icon class="ms-3">mdi-cart-outline</v-icon>
                    </template>
                    <v-toolbar-title class="text-subtitle-1 font-weight-bold">
                        Current Transactions
                    </v-toolbar-title>
                    <template #append>
                        <v-btn
                            v-if="cart.length > 0"
                            icon
                            size="small"
                            variant="tonal"
                            color="error"
                            class="cart-clear-btn mr-3"
                            :disabled="isCheckoutLocked"
                            @click="clearCartDialog = true"
                        >
                            <v-icon icon="mdi-cart-remove" size="20" color="error"></v-icon>
                            <v-tooltip activator="parent" location="bottom">Clear cart</v-tooltip>
                        </v-btn>
                    </template>
                </v-toolbar>

                <!-- Cart Items -->
                <div class="flex-grow-1 overflow-y-auto pa-3 rounded-xl">
                    <div v-if="cart.length === 0" class="h-100 d-flex flex-column align-center justify-center text-grey rounded-xl">
                        <v-icon size="64" class="mb-4">mdi-cart-remove</v-icon>
                        <div class="text-body-2">Cart is empty</div>
                        <div class="text-caption mt-1">Click a product to add it</div>
                    </div>
                    <v-list v-else lines="three" color="primary" bg-color="transparent">
                        <v-list-item
                            v-for="(item, index) in cart"
                            :key="index"
                            variant="solo-filled"
                            color="primary"
                            class="mb-2 rounded-lg border"
                        >
                            <template v-slot:title>
                                <div class="text-body-2 font-weight-bold text-wrap mb-1">{{ item.name }}</div>
                            </template>
                            <template v-slot:subtitle>
                                <div class="text-caption">{{ formatPrice(item.unit_price) }}</div>
                            </template>
                            <template v-slot:append>
                                <div class="d-flex flex-column align-end">
                                    <div class="d-flex align-center mb-1">
                                        <v-btn icon="mdi-minus" size="x-small" variant="tonal" color="primary" @click="updateQty(item, -1)" :disabled="isCheckoutLocked"></v-btn>
                                        <span class="mx-3 font-weight-bold">{{ item.qty }}</span>
                                        <v-btn icon="mdi-plus" size="x-small" variant="tonal" color="primary" @click="updateQty(item, 1)" :disabled="isCheckoutLocked || item.qty >= item.stock_qty"></v-btn>
                                    </div>
                                    <div class="font-weight-bold text-body-2 text-primary">
                                        {{ formatPrice(item.subtotal) }}
                                    </div>
                                </div>
                            </template>
                        </v-list-item>
                    </v-list>
                </div>

                <!-- Checkout Summary -->
                <div class="pa-4" style="border-top: 1px solid rgba(0,0,0,0.12);">
                    <div class="d-flex justify-space-between mb-2 text-caption">
                        <span class="text-grey-darken-1">Subtotal</span>
                        <span>{{ formatPrice(subtotal) }}</span>
                    </div>
                    <div class="d-flex justify-space-between mb-3 text-caption pb-2" style="border-bottom: 1px solid rgba(0,0,0,0.08);">
                        <span class="text-grey-darken-1">Tax (10%)</span>
                        <span>{{ formatPrice(tax) }}</span>
                    </div>
                    <div class="d-flex justify-space-between mb-4">
                        <span class="font-weight-bold text-subtitle-1">Total</span>
                        <span class="font-weight-bold text-subtitle-1">{{ formatPrice(total) }}</span>
                    </div>

                    <v-select
                        v-model="paymentMethod"
                        :items="[{title: 'Cash', value: 'cash'}, {title: 'QRIS', value: 'qris'}]"
                        label="Payment Method"
                        variant="outlined"
                        density="compact"
                        class="mb-3"
                        :disabled="isCheckoutLocked"
                    >
                        <template v-slot:prepend-inner>
                            <v-icon>{{ paymentMethod === 'cash' ? 'mdi-cash' : 'mdi-qrcode' }}</v-icon>
                        </template>
                    </v-select>

                    <v-number-input
                        id="input-amount-paid"
                        clearable
                        v-if="paymentMethod === 'cash'"
                        v-model="amountPaid"
                        label="Amount Paid"
                        variant="outlined"
                        density="compact"
                        class="mb-3"
                        :disabled="isCheckoutLocked"
                    >
                        <template v-slot:prepend-inner>
                            <span class="mr-1">Rp</span>
                        </template>
                    </v-number-input>

                    <v-text-field
                        v-if="paymentMethod === 'cash'"
                        :model-value="formatPrice(change)"
                        readonly
                        label="Kembalian"
                        variant="outlined"
                        density="compact"
                        class="mb-3"
                    ></v-text-field>

                    <v-btn
                        id="btn-complete-transaction"
                        block
                        size="large"
                        @click="checkout"
                        :loading="isCheckoutLocked"
                        :disabled="cart.length === 0 || isCheckoutLocked"
                        color="primary"
                        rounded="lg"
                    >
                        <v-icon start>mdi-check-circle-outline</v-icon>
                        Complete Transaction
                    </v-btn>
                </div>
            </div>
        </template>

        <!-- ── Column 2: Products Section ── -->
        <div class="d-flex flex-column" style="height: calc(100vh - 80px);">
            <!-- Search & Filter -->
            <v-text-field
                id="input-search-product"
                ref="searchInput"
                label="Search"
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                rounded="lg"
                v-model="search"
                class="flex-shrink-0 flex-grow-0 text-grey"
                density="comfortable"
                clearable
                :disabled="isCheckoutLocked"
            ></v-text-field>

            <v-chip-group v-model="selectedCategory" class="category-chip-scroller mb-3 flex-shrink-0" mandatory :disabled="isCheckoutLocked">
                <v-chip :value="null" filter variant="outlined" color="primary" class="border text-grey" :disabled="isCheckoutLocked">All</v-chip>
                <v-chip
                    v-for="cat in categories"
                    :key="cat.id"
                    :value="cat.id"
                    filter
                    variant="outlined"
                    color="primary"
                    class="border text-grey"
                    :disabled="isCheckoutLocked"
                >
                    {{ cat.name }}
                </v-chip>
            </v-chip-group>

            <!-- Product Grid -->
            <div class="flex-grow-1 overflow-y-auto">
                <v-container fluid class="pa-2">
                    <v-row>
                        <v-col
                            v-for="product in filteredProducts"
                            :key="product.id"
                            cols="12" sm="6" md="4" lg="3" xl="2"
                        >
                            <v-card
                                :id="'product-card-' + product.id"
                                class="test-product-card h-100 d-flex flex-column rounded-xl border"
                                :class="{ 'checkout-disabled': isCheckoutLocked }"
                                @click="addToCart(product)"
                                :hover="!isCheckoutLocked"
                                flat
                                :disabled="isCheckoutLocked"
                            >
                                <div class="product-image-frame">
                                    <v-img v-if="product.image_url" :src="product.image_url" contain height="100%" width="100%"></v-img>
                                    <div v-else class="product-image-fallback">
                                        <v-icon size="42" color="grey-lighten-1">mdi-image-outline</v-icon>
                                    </div>
                                </div>
                                <v-card-text class="product-card-body flex-grow-1">
                                    <div class="product-name font-weight-bold text-title-medium">{{ product.name }}</div>
                                    <div class="text-caption text-grey-darken-1">{{ product.category?.name || 'Uncategorized' }}</div>
                                </v-card-text>
                                <v-card-actions class="product-card-actions px-4 pb-4 pt-0">
                                    <div class="product-price font-weight-bold text-primary text-title-medium">{{ formatPrice(product.sell_price) }}</div>
                                    <v-chip class="product-stock-chip" size="x-small" :color="getStockChipColor(product)">
                                        {{ product.stock_qty }} left
                                    </v-chip>
                                </v-card-actions>
                            </v-card>
                        </v-col>
                        <v-col v-if="filteredProducts.length === 0" cols="12" class="text-center py-10">
                            <v-icon size="64" color="grey">mdi-package-variant-closed</v-icon>
                            <div class="text-grey mt-4">No products found.</div>
                        </v-col>
                    </v-row>
                </v-container>
            </div>
        </div>

        <v-snackbar v-model="snackbar" :timeout="2200" :color="snackbarColor" location="bottom right">
            {{ snackbarMessage }}
        </v-snackbar>

        <v-dialog v-model="clearCartDialog" max-width="380">
            <v-card class="rounded-xl">
                <v-card-title class="pa-4 font-weight-bold d-flex align-center">
                    <v-icon color="error" class="mr-2">mdi-cart-remove</v-icon>
                    Clear Cart
                </v-card-title>
                <v-card-text class="px-4 pb-2">
                    Remove all products from the current cart?
                </v-card-text>
                <v-card-actions class="pa-4">
                    <v-spacer></v-spacer>
                    <v-btn variant="text" class="text-none" rounded="lg" @click="clearCartDialog = false">
                        Cancel
                    </v-btn>
                    <v-btn color="error" variant="flat" class="text-none" rounded="lg" @click="clearCart">
                        Clear Cart
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-overlay
            :model-value="isQrisFinalizing"
            persistent
            scrim="surface"
            class="align-center justify-center text-center"
        >
            <div class="d-flex flex-column align-center ga-3">
                <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
                <div class="text-subtitle-2 font-weight-bold">{{ qrisStatusMessage || 'Updating stock...' }}</div>
                <div class="text-caption text-grey-darken-1">Please wait until the latest data is loaded.</div>
            </div>
        </v-overlay>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Custom scrollbar for cart items */
::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: #e0e0e0;
  border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
  background: #bdbdbd;
}

.checkout-disabled {
  pointer-events: none;
  opacity: 0.72;
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

.product-image-fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(var(--v-theme-primary), 0.045);
}

.product-image-frame {
  width: 100%;
  height: 160px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background: rgba(var(--v-theme-primary), 0.045);
}

.product-card-body {
  min-width: 0;
}

.product-name {
  overflow-wrap: anywhere;
  line-height: 1.25;
}

.product-card-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  flex-wrap: wrap;
  min-width: 0;
}

.product-price {
  min-width: 0;
  overflow-wrap: anywhere;
  line-height: 1.25;
}

.product-stock-chip {
  flex: 0 1 auto;
  max-width: 100%;
}

.product-stock-chip :deep(.v-chip__content) {
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
}

.cart-clear-btn {
  border: 1px solid rgba(var(--v-theme-error), 0.28);
  background: rgba(var(--v-theme-error), 0.1) !important;
}

.cart-clear-btn :deep(.v-icon) {
  color: rgb(var(--v-theme-error)) !important;
  opacity: 1;
}
</style>

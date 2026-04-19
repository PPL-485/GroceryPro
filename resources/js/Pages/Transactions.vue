<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useTheme } from 'vuetify';

const props = defineProps({
    categories: Array,
    products: Array,
});

const theme = useTheme()

const search = ref('');
const selectedCategory = ref(null);
const cart = ref([]);
const paymentMethod = ref('cash');
const amountPaid = ref(0);
const snackbar = ref(false);
const snackbarMessage = ref('');

// Computed properties
const filteredProducts = computed(() => {
    let filtered = props.products;
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

const tax = computed(() => {
    return Math.round(subtotal.value * 0.10); // 10% tax
});

const total = computed(() => {
    return Math.round(subtotal.value + tax.value);
});

import { watch } from 'vue';
watch(paymentMethod, (newVal) => {
    if (newVal === 'cash' && amountPaid.value === 0) {
        amountPaid.value = total.value;
    }
});

// Methods
const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
};

const addToCart = (product) => {
    if (product.stock_qty <= 0) {
        snackbarMessage.value = 'Product out of stock!';
        snackbar.value = true;
        return;
    }
    const existing = cart.value.find(i => i.product_id === product.id);
    if (existing) {
        if (existing.qty < product.stock_qty) {
            existing.qty++;
            existing.subtotal = existing.qty * existing.unit_price;
        } else {
            snackbarMessage.value = 'Cannot add more than available stock!';
            snackbar.value = true;
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
    }
};

const updateQty = (item, delta) => {
    const newQty = item.qty + delta;
    if (newQty > 0 && newQty <= item.stock_qty) {
        item.qty = newQty;
        item.subtotal = item.qty * item.unit_price;
    } else if (newQty === 0) {
        cart.value = cart.value.filter(i => i.product_id !== item.product_id);
    }
};

const form = useForm({
    items: [],
    payment_method: 'cash',
    total_amount: 0,
});

const checkout = () => {
    if (cart.value.length === 0) {
        snackbarMessage.value = 'Cart is empty!';
        snackbar.value = true;
        return;
    }
    
    if (paymentMethod.value === 'cash' && amountPaid.value < total.value) {
        snackbarMessage.value = 'Amount paid is less than total!';
        snackbar.value = true;
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

    form.post(route('transactions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            cart.value = [];
            amountPaid.value = 0;
            snackbarMessage.value = 'Transaction completed successfully!';
            snackbar.value = true;
        },
        onError: (errors) => {
            console.error(errors);
            snackbarMessage.value = errors.error || Object.values(errors)[0] || 'An error occurred during checkout.';
            snackbar.value = true;
        }
    });
};
</script>

<template>
    <Head title="Point of Sale" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl">Point of Sale (POS)</h2>
        </template> 

        <template #header-description>
            <p class="text-sm">
                Scan or search products to add to cart
            </p>
        </template>
        
        <v-container fluid class="pa-0 h-100">
            <v-row class="h-100 ma-0">
                <!-- Products Section -->
                <v-col cols="12" md="8" class="pa-4 d-flex flex-column" style="height: calc(100vh - 120px);">
                    <!-- Search & Filter -->
                     
                    <v-text-field 
                        label="Search" 
                        prepend-inner-icon="mdi-magnify" 
                        variant="solo"
                        class="flex-grow-0 flex-shrink-0 mb-4"
                    ></v-text-field>
                    <v-chip-group v-model="selectedCategory" column mandatory>
                    <v-chip
                        :value="null"
                        filter
                        :color="theme.global.name.value === 'brand' ? 'primary' : undefined"
                    >
                        All
                    </v-chip>

                    <v-chip
                        v-for="cat in categories"
                        :key="cat.id"
                        :value="cat.id"
                        filter
                        :color="theme.global.name.value === 'brand' ? 'primary' : undefined"
                    >
                        {{ cat.name }}
                    </v-chip>
                    </v-chip-group>

                    <!-- Product Grid -->
                    <div class="flex-grow-1 overflow-y-auto">
                        <v-container fluid class="pa-2">
                        <v-row>
                        <v-col v-for="product in filteredProducts" :key="product.id" cols="12" sm="6" md="4" lg="3">
                            <v-card @click="addToCart(product)" hover class="h-100 d-flex flex-column rounded-xl" flat>
                                <div class="d-flex align-center justify-center rounded-t-xl" style="height: 160px;">
                                    <v-icon size="64">mdi-cart-outline</v-icon>
                                </div>
                                <v-card-text class="flex-grow-1">
                                    <div class="font-weight-bold mb-1">{{ product.name }}</div>
                                    <div class="text-caption">{{ product.category?.name || 'Uncategorized' }}</div>
                                </v-card-text>
                                <v-card-actions class="d-flex justify-space-between align-center px-4 pb-4 pt-0">
                                    <div class="text-body-1 font-weight-bold">{{ formatPrice(product.sell_price) }}</div>
                                    <v-chip size="x-small" :color="product.stock_qty > 10 ? 'default' : 'error'">
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
                </v-col>

                <!-- Cart Section -->
                <v-col cols="12" md="4" class="pa-0 d-flex flex-column border-s" style="height: calc(100vh - 120px);">
                    <v-toolbar color="transparent" border="b" class="px-4" flat>
                        <v-icon class="mr-2">mdi-cart-outline</v-icon>
                        <v-toolbar-title class="text-h6 font-weight-bold pl-0">Current Transaction</v-toolbar-title>
                    </v-toolbar>

                    <!-- Cart Items -->
                    <div class="flex-grow-1 overflow-y-auto pa-4">
                        <div v-if="cart.length === 0" class="h-100 d-flex flex-column align-center justify-center text-grey">
                            <v-icon size="64" class="mb-4">mdi-cart-remove</v-icon>
                            <div>Cart is empty</div>
                        </div>
                        <v-list v-else lines="two" class="pa-0">
                            <v-list-item v-for="(item, index) in cart" :key="index">
                                <template v-slot:title>
                                    <div class="text-body-2 font-weight-bold text-wrap mb-1">{{ item.name }}</div>
                                </template>
                                <template v-slot:subtitle>
                                    <div class="text-caption">{{ formatPrice(item.unit_price) }}</div>
                                </template>
                                <template v-slot:append>
                                    <div class="d-flex align-center mt-2">
                                        <v-btn icon="mdi-minus" size="x-small" variant="tonal" @click="updateQty(item, -1)"></v-btn>
                                        <span class="mx-3 font-weight-bold">{{ item.qty }}</span>
                                        <v-btn icon="mdi-plus" size="x-small" variant="tonal" @click="updateQty(item, 1)" :disabled="item.qty >= item.stock_qty"></v-btn>
                                    </div>
                                    <div class="ml-4 font-weight-bold text-right text-body-2" style="min-width: 80px;">
                                        {{ formatPrice(item.subtotal) }}
                                    </div>
                                </template>
                            </v-list-item>
                        </v-list>
                    </div>

                    <!-- Checkout Summary -->
                    <div class="pa-4 border-t">
                        <div class="d-flex justify-space-between mb-2 text-caption">
                            <span class="text-grey-darken-1">Subtotal</span>
                            <span>{{ formatPrice(subtotal) }}</span>
                        </div>
                        <div class="d-flex justify-space-between mb-4 text-caption border-b pb-2">
                            <span class="text-grey-darken-1">Tax (10%)</span>
                            <span>{{ formatPrice(tax) }}</span>
                        </div>
                        <div class="d-flex justify-space-between mb-6">
                            <span class="font-weight-bold text-subtitle-1">Total</span>
                            <span class="font-weight-bold text-subtitle-1 text-success">{{ formatPrice(total) }}</span>
                        </div>

                        <v-select
                            v-model="paymentMethod"
                            :items="[{title: 'Cash', value: 'cash'}, {title: 'QRIS', value: 'qris'}]"
                            label="Payment Method"
                            density="compact"
                        >
                            <template v-slot:prepend-inner>
                                <v-icon size="small" class="mr-1">{{ paymentMethod === 'cash' ? 'mdi-cash' : 'mdi-qrcode' }}</v-icon>
                            </template>
                        </v-select>
                        <v-text-field
                            v-if="paymentMethod === 'cash'"
                            v-model="amountPaid"
                            type="number"
                            label="Amount Paid"
                        >
                            <template v-slot:prepend-inner>
                                <span class="mr-1 mt-1">Rp</span>
                            </template>
                        </v-text-field>

                        <v-btn
                            block
                            size="large"
                            @click="checkout"
                            :loading="form.processing"
                            :disabled="cart.length === 0"
                        >
                            Complete Transaction
                        </v-btn>
                    </div>
                </v-col>
            </v-row>
        </v-container>

        <v-snackbar v-model="snackbar" :timeout="3000" color="success" location="bottom right">
            {{ snackbarMessage }}
        </v-snackbar>
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
</style>

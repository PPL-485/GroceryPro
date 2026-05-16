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
                <v-toolbar title="Current Transactions" flat color="surface">
                    <template #prepend>
                        <v-icon class="ms-3">mdi-cart-outline</v-icon>
                    </template>
                    <template #append>
                        <v-chip size="small" color="white" class="mr-3" v-if="cart.length > 0">
                            {{ cart.length }} item{{ cart.length !== 1 ? 's' : '' }}
                        </v-chip>
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
                                        <v-btn icon="mdi-minus" size="x-small" variant="tonal" color="primary" @click="updateQty(item, -1)"></v-btn>
                                        <span class="mx-3 font-weight-bold">{{ item.qty }}</span>
                                        <v-btn icon="mdi-plus" size="x-small" variant="tonal" color="primary" @click="updateQty(item, 1)" :disabled="item.qty >= item.stock_qty"></v-btn>
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
                    >
                        <template v-slot:prepend-inner>
                            <v-icon>{{ paymentMethod === 'cash' ? 'mdi-cash' : 'mdi-qrcode' }}</v-icon>
                        </template>
                    </v-select>

                    <v-number-input
                        clearable
                        v-if="paymentMethod === 'cash'"
                        v-model="amountPaid"
                        label="Amount Paid"
                        variant="outlined"
                        density="compact"
                        class="mb-3"
                    >
                        <template v-slot:prepend-inner>
                            <span class="mr-1">Rp</span>
                        </template>
                    </v-number-input>

                    <v-btn
                        block
                        size="large"
                        @click="checkout"
                        :loading="form.processing"
                        :disabled="cart.length === 0"
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
                label="Search"
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                rounded="lg"
                v-model="search"
                class="flex-shrink-0 flex-grow-0 text-grey"
                density="comfortable"
                clearable
            ></v-text-field>

            <v-chip-group v-model="selectedCategory" column class="mb-3 flex-shrink-0" mandatory>
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

            <!-- Product Grid -->
            <div class="flex-grow-1 overflow-y-auto">
                <v-container fluid class="pa-2">
                    <v-row>
                        <v-col
                            v-for="product in filteredProducts"
                            :key="product.id"
                            cols="12" sm="6" md="4" lg="2"
                        >
                            <v-card @click="addToCart(product)" hover flat class="h-100 d-flex flex-column rounded-xl border">
                                <div class="d-flex align-center justify-center overflow-hidden cover" style="height: 160px;">
                                    <v-img v-if="product.image_url" :src="product.image_url" cover height="100%" width="100%"></v-img>
                                </div>
                                <v-card-text class="flex-grow-1">
                                    <div class="font-weight-bold text-title-medium">{{ product.name }}</div>
                                    <div class="text-caption text-grey-darken-1">{{ product.category?.name || 'Uncategorized' }}</div>
                                </v-card-text>
                                <v-card-actions class="d-flex justify-space-between align-center px-4 pb-4 pt-0">
                                    <div class="font-weight-bold text-primary text-title-medium">{{ formatPrice(product.sell_price) }}</div>
                                    <v-chip size="x-small" :color="product.stock_qty > 10 ? 'primary' : 'error'">
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

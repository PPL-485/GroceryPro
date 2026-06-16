<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import AppBar from '@/Components/AppBar.vue';
import { ref, provide, useSlots, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useDisplay } from 'vuetify';

defineProps({
    transactionCartItemCount: {
        type: Number,
        default: 0,
    },
    transactionCartSubtotal: {
        type: Number,
        default: 0,
    },
});

// Detect whether the current page fills the transactions-sidebar slot
const slots = useSlots();
const hasTransactionsSidebar = computed(() => !!slots['transactions-sidebar']);
const page = usePage();
const { mdAndUp } = useDisplay();
const navigationDrawer = ref(mdAndUp.value);

// Right-panel state — shared with AppBar via provide/inject
const transactionsDrawer = ref(false);
const snackbar = ref({
    show: false,
    text: '',
    color: 'success',
});

const flashMessage = computed(() => {
    const flash = page.props.flash || {};
    if (flash.success) return { text: flash.success, color: 'success' };
    if (flash.error) return { text: flash.error, color: 'error' };
    if (flash.status) return { text: flash.status, color: 'success' };
    return null;
});

const toggleTransactionsDrawer = () => {
    transactionsDrawer.value = !transactionsDrawer.value;
};

const toggleNavigationDrawer = () => {
    navigationDrawer.value = !navigationDrawer.value;
};

provide('transactionsDrawer', transactionsDrawer);
provide('toggleTransactionsDrawer', toggleTransactionsDrawer);
provide('hasTransactionsSidebar', hasTransactionsSidebar);
provide('toggleNavigationDrawer', toggleNavigationDrawer);

watch(mdAndUp, (value) => {
    navigationDrawer.value = value;
    if (!value) transactionsDrawer.value = false;
});

watch(flashMessage, (message) => {
    if (!message) return;

    snackbar.value = {
        show: true,
        text: message.text,
        color: message.color,
    };
}, { immediate: true });
</script>

<template>
    <v-app>
        <!-- Column 1: Left Navigation Sidebar (Vuetify handles offset for v-main) -->
        <Sidebar v-model="navigationDrawer" />

        <!-- v-main fills the remaining space after the left nav drawer -->
        <v-main class="main-wrapper">
            <div class="layout-row">

                <!-- Column 2: Center (AppBar + page content) -->
                <div class="center-column">
                    <!-- AppBar is scoped to this column, not full-width -->
                    <AppBar :cart-item-count="transactionCartItemCount" :cart-subtotal="transactionCartSubtotal">
                        <template #title>
                            <slot name="header-title" />
                        </template>
                        <template #description>
                            <slot name="header-description" />
                        </template>
                    </AppBar>

                    <!-- Page Content -->
                    <div class="content-area">
                        <div class="content-inner">
                            <slot />
                        </div>
                    </div>
                </div>

                <!-- Column 3: Right Transactions Sidebar (persistent, inline, no overlay) -->
                <transition name="slide-panel">
                    <div v-if="transactionsDrawer && mdAndUp" class="transactions-panel">
                        <slot name="transactions-sidebar">
                            <!-- Default placeholder when no page provides content -->
                            <div class="d-flex flex-column align-center justify-center h-100 text-grey pa-8">
                                <v-icon size="56" class="mb-4">mdi-receipt-text-outline</v-icon>
                                <div class="text-subtitle-1 font-weight-medium mb-2">Transactions Panel</div>
                                <div class="text-caption text-center">
                                    This panel is available on pages that support transaction tracking.
                                </div>
                            </div>
                        </slot>
                    </div>
                </transition>

            </div>
        </v-main>

        <v-navigation-drawer
            v-if="hasTransactionsSidebar && !mdAndUp"
            v-model="transactionsDrawer"
            location="right"
            temporary
            width="360"
        >
            <slot name="transactions-sidebar">
                <div class="d-flex flex-column align-center justify-center h-100 text-grey pa-8">
                    <v-icon size="56" class="mb-4">mdi-receipt-text-outline</v-icon>
                    <div class="text-subtitle-1 font-weight-medium mb-2">Transactions Panel</div>
                    <div class="text-caption text-center">
                        This panel is available on pages that support transaction tracking.
                    </div>
                </div>
            </slot>
        </v-navigation-drawer>

        <v-snackbar
            v-model="snackbar.show"
            :color="snackbar.color"
            timeout="3000"
            location="bottom right"
        >
            {{ snackbar.text }}
            <template #actions>
                <v-btn
                    color="white"
                    variant="text"
                    @click="snackbar.show = false"
                >
                    Close
                </v-btn>
            </template>
        </v-snackbar>
    </v-app>
</template>

<style scoped>
/* Make v-main and its inner Vuetify wrapper fill the full remaining height */
.main-wrapper {
    height: 100vh;
    background: rgb(var(--v-theme-surface));
}

/* Vuetify renders a .v-main__wrap div inside v-main — ensure it stretches too */
:deep(.v-main__wrap) {
    height: 100%;
    display: flex;
    flex-direction: column;
}

/* Flex row spanning the full height of v-main */
.layout-row {
    display: flex;
    height: 100%;
    overflow: hidden;
}

/* Center column: vertical flex (AppBar on top, content below) */
.center-column {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
    overflow: hidden;
    background: rgb(var(--v-theme-surface));
}

/* Scrollable content area below the AppBar */
.content-area {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    background:
        radial-gradient(circle at top left, rgba(var(--v-theme-primary), 0.08), transparent 32rem),
        linear-gradient(180deg, rgba(var(--v-theme-surface), 1), rgba(var(--v-theme-primary), 0.035));
}

.content-inner {
    padding: 24px 16px;
}

.content-inner :deep(.v-card) {
    box-shadow: none !important;
    border: 1px solid rgba(var(--v-border-color), 0.18) !important;
    background-color: rgb(var(--v-theme-surface)) !important;
}

.content-inner :deep(.v-card:hover) {
    box-shadow: none !important;
}

.content-inner :deep(.v-card.rounded-lg),
.content-inner :deep(.v-card.rounded-xl) {
    border-radius: 18px !important;
}

.content-inner :deep(.dashboard-clickable-card),
.content-inner :deep(.test-product-card),
.content-inner :deep(.v-card[role="button"]) {
    transition: transform 0.18s ease, border-color 0.18s ease, background-color 0.18s ease !important;
}

.content-inner :deep(.dashboard-clickable-card:hover),
.content-inner :deep(.test-product-card:hover),
.content-inner :deep(.v-card[role="button"]:hover) {
    transform: translateY(-2px);
    border-color: rgba(var(--v-theme-primary), 0.34) !important;
    background-color: rgba(var(--v-theme-primary), 0.035) !important;
}

.content-inner :deep(.v-card-title) {
    letter-spacing: 0;
}

.content-inner :deep(.v-table) {
    background: transparent !important;
}

.content-inner :deep(.v-table thead th) {
    background: transparent !important;
    color: rgba(var(--v-theme-on-surface), 0.72) !important;
    font-size: 0.78rem;
    letter-spacing: 0;
}

.content-inner :deep(.v-table tbody tr:hover) {
    background: rgba(var(--v-theme-primary), 0.035) !important;
}

.content-inner :deep(.v-field) {
    border-radius: 14px;
}

@media (max-width: 959px) {
    .content-inner {
        padding: 16px 10px;
    }
}

/* Right sidebar: persistent panel, no overlay, pushes center column */
.transactions-panel {
    width: 380px;
    min-width: 380px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border-left: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

/* Slide-in/out animation */
.slide-panel-enter-active,
.slide-panel-leave-active {
    transition: width 0.28s cubic-bezier(0.4, 0, 0.2, 1),
                min-width 0.28s cubic-bezier(0.4, 0, 0.2, 1),
                opacity 0.2s ease;
}
.slide-panel-enter-from,
.slide-panel-leave-to {
    width: 0 !important;
    min-width: 0 !important;
    opacity: 0;
}
</style>

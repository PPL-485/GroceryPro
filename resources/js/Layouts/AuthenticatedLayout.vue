<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import AppBar from '@/Components/AppBar.vue';
import { ref, provide, useSlots, computed } from 'vue';

// Detect whether the current page fills the transactions-sidebar slot
const slots = useSlots();
const hasTransactionsSidebar = computed(() => !!slots['transactions-sidebar']);

// Right-panel state — shared with AppBar via provide/inject
const transactionsDrawer = ref(false);

const toggleTransactionsDrawer = () => {
    transactionsDrawer.value = !transactionsDrawer.value;
};

provide('transactionsDrawer', transactionsDrawer);
provide('toggleTransactionsDrawer', toggleTransactionsDrawer);
provide('hasTransactionsSidebar', hasTransactionsSidebar);
</script>

<template>
    <v-app>
        <!-- Column 1: Left Navigation Sidebar (Vuetify handles offset for v-main) -->
        <Sidebar />

        <!-- v-main fills the remaining space after the left nav drawer -->
        <v-main class="main-wrapper">
            <div class="layout-row">

                <!-- Column 2: Center (AppBar + page content) -->
                <div class="center-column">
                    <!-- AppBar is scoped to this column, not full-width -->
                    <AppBar>
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
                    <div v-if="transactionsDrawer" class="transactions-panel">
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
    </v-app>
</template>

<style scoped>
/* Make v-main and its inner Vuetify wrapper fill the full remaining height */
.main-wrapper {
    height: 100vh;
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
}

/* Scrollable content area below the AppBar */
.content-area {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
}

.content-inner {
    padding: 24px 16px;
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

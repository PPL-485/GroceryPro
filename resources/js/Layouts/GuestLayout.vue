<template>
    <v-app class="guest-app">
        <v-main>
            <v-container class="guest-shell" fluid>
                <div class="guest-panel">
                    <slot />
                </div>
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>
.guest-app {
    background: rgb(var(--v-theme-surface));
    font-family: "Plus Jakarta Sans", "Inter", sans-serif;
}

.guest-shell {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background:
        radial-gradient(circle at top left, rgba(var(--v-theme-primary), 0.08), transparent 32rem),
        linear-gradient(135deg, rgba(var(--v-theme-primary), 0.04), rgba(var(--v-theme-surface), 0) 42%);
}

.guest-panel {
    width: 100%;
    max-width: 420px;
}

@media (max-width: 600px) {
    .guest-shell {
        padding: 16px;
    }

    .guest-panel {
        max-width: 100%;
    }
}
</style>

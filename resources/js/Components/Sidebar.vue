<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'

const logout = () => {
    router.post(route('logout'))
}

const page = usePage()
const user = page.props.auth.user
</script>

<template>
    <v-navigation-drawer
        expand-on-hover
        permanent
        rail
    >
        <v-list>
            <v-list-item
                prepend-avatar="https://randomuser.me/api/portraits/women/85.jpg"
                :title="user.name"
                :subtitle="user.email"
            ></v-list-item>
        </v-list>

        <v-divider></v-divider>

        <v-list density="compact" nav>
            <v-list-item :component="Link" href="/dashboard" prepend-icon="mdi-view-dashboard-outline" title="Dashboard"></v-list-item>
            <v-list-item :component="Link" href="/goods" prepend-icon="mdi-package-variant" title="Goods/Stock"></v-list-item>
            <v-list-item :component="Link" href="/transactions" prepend-icon="mdi-cash" title="Transactions (POS)"></v-list-item>
            <v-list-item :component="Link" href="/reports" prepend-icon="mdi-chart-box-outline" title="Report"></v-list-item>
            <v-list-item :component="Link" href="/user-management" prepend-icon="mdi-account-group-outline" title="User Management"></v-list-item>
            <v-list-item :component="Link" href="/category-management" prepend-icon="mdi-shape-plus-outline" title="Category Management"></v-list-item>
            <v-list-item :component="Link" href="/settings" prepend-icon="mdi-cog-outline" title="Settings"></v-list-item>
        </v-list>

        <template #append>
            <v-divider></v-divider>
            <v-list density="compact" nav>
                <v-list-item
                    prepend-icon="mdi-logout"
                    title="Log Out"
                    base-color="error"
                    @click="logout"
                ></v-list-item>
            </v-list>
        </template>
    </v-navigation-drawer>
</template>
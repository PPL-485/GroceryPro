<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'

const logout = () => {
    router.post(route('logout'))
}

const page = usePage()
const user = page.props.auth.user

const menus = [
    { name: 'Dashboard', href: '/dashboard', icon: 'mdi-view-dashboard-outline', roles: ['admin', 'cashier'] },

    { name: 'Goods/Stock', href: '/goods', icon: 'mdi-package-variant', roles: ['admin'] },

    { name: 'POS', href: '/transactions', icon: 'mdi-cash', roles: ['cashier', 'admin'] },

    { name: 'Report', href: '/report', icon: 'mdi-chart-box-outline', roles: ['admin'] },

    { name: 'User Management', href: '/users', icon: 'mdi-account-group-outline', roles: ['admin'] },

    { name: 'Category Management', href: '/categories', icon: 'mdi-shape-plus-outline', roles: ['admin'] },

    { name: 'Settings', href: '/settings', icon: 'mdi-cog-outline', roles: ['cashier', 'admin'] },
]

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
            <v-list-item
            v-for="menu in menus.filter(m => m.roles.includes(user.role))"
            :key="menu.href"
            :href="menu.href"
            :title="menu.name"
            :prepend-icon="menu.icon"
            />
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
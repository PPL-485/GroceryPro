<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    categories: Array,
});

const search = ref('');
const dialog = ref(false);
const snackbar = ref(false);
const snackbarMessage = ref('');

const form = useForm({
    name: '',
    description: '',
});

const filteredCategories = computed(() => {
    if (!search.value) return props.categories;
    const lowerSearch = search.value.toLowerCase();
    return props.categories.filter(cat => 
        cat.name.toLowerCase().includes(lowerSearch) || 
        (cat.description && cat.description.toLowerCase().includes(lowerSearch))
    );
});

const formatId = (id) => {
    return 'CAT-' + id.toString().padStart(3, '0');
};

const submit = () => {
    form.post(route('categories.store'), {
        preserveScroll: true,
        onSuccess: () => {
            dialog.value = false;
            form.reset();
            snackbarMessage.value = 'Category added successfully!';
            snackbar.value = true;
        },
        onError: (errors) => {
            snackbarMessage.value = 'Failed to add category. Please check your inputs.';
            snackbar.value = true;
        }
    });
};
</script>

<template>
    <Head title="Category Management" />

    <AuthenticatedLayout>
        <template #header-title>
            Category Management
        </template>

        <template #header-description>
            <p class="text-sm">
                Organize your products into categories
            </p>
        </template>

        <v-container fluid class="pa-0 mt-4">
            <v-row class="mb-6" align="center">
                <v-col cols="12" md="5">
                    <v-text-field
                        v-model="search"
                        prepend-inner-icon="mdi-magnify"
                        placeholder="Search categories..."
                        variant="outlined"
                        density="compact"
                        hide-details
                        rounded="lg"
                        bg-color="white"
                        class="search-input"
                    ></v-text-field>
                </v-col>
                <v-spacer></v-spacer>
                <v-col cols="auto">
                    <v-btn color="#C87A54" rounded="lg" @click="dialog = true" class="text-none px-6" elevation="0" height="40">
                        <v-icon start size="small">mdi-plus</v-icon>
                        <span class="font-weight-medium">Add Category</span>
                    </v-btn>
                </v-col>
            </v-row>

            <v-row>
                <v-col v-for="category in filteredCategories" :key="category.id" cols="12" sm="6" md="4" lg="3">
                    <v-card class="category-card rounded-xl border pa-5" elevation="0">
                        <!-- Top Row: Icon & Actions -->
                        <div class="d-flex justify-space-between align-start mb-4">
                            <div class="icon-box">
                                <v-icon color="#2E5A27">mdi-shape-outline</v-icon>
                            </div>
                            <div class="d-flex gap-2">
                                <v-btn icon size="x-small" variant="text" color="#2E5A27" class="action-btn">
                                    <v-icon size="small">mdi-pencil-outline</v-icon>
                                </v-btn>
                                <v-btn icon size="x-small" variant="text" color="#C87A54" class="action-btn">
                                    <v-icon size="small">mdi-trash-can-outline</v-icon>
                                </v-btn>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="text-h6 font-weight-bold mb-2 text-primary-dark">
                            {{ category.name }}
                        </div>
                        <div class="text-body-2 text-grey-darken-1 mb-4 desc-text">
                            {{ category.description || 'No description provided' }}
                        </div>

                        <!-- Product Count -->
                        <div class="d-flex align-center text-grey-darken-1 text-body-2 font-weight-medium mb-4">
                            <v-icon size="small" class="mr-2">mdi-package-variant-closed</v-icon>
                            {{ category.products_count || 0 }} products
                        </div>

                        <v-divider class="mb-4" color="#E0E0E0"></v-divider>

                        <!-- Footer -->
                        <div>
                            <div class="text-caption text-grey mb-1">Category ID</div>
                            <div class="text-subtitle-2 font-weight-bold text-primary-dark">{{ formatId(category.id) }}</div>
                        </div>
                    </v-card>
                </v-col>
                
                <v-col v-if="filteredCategories.length === 0" cols="12" class="text-center py-10">
                    <div class="text-grey">No categories found.</div>
                </v-col>
            </v-row>
        </v-container>

        <!-- Add Category Dialog -->
        <v-dialog v-model="dialog" max-width="500">
            <v-card class="rounded-xl">
                <v-card-title class="pa-4 font-weight-bold d-flex justify-space-between align-center border-b">
                    Add New Category
                    <v-btn icon="mdi-close" variant="text" size="small" @click="dialog = false"></v-btn>
                </v-card-title>
                
                <v-card-text class="pa-4">
                    <v-form @submit.prevent="submit">
                        <div class="mb-2 text-subtitle-2 font-weight-medium">Name</div>
                        <v-text-field
                            v-model="form.name"
                            variant="outlined"
                            density="comfortable"
                            placeholder="Enter category name"
                            :error-messages="form.errors.name"
                            class="mb-4"
                            rounded="lg"
                        ></v-text-field>

                        <div class="mb-2 text-subtitle-2 font-weight-medium">Description</div>
                        <v-textarea
                            v-model="form.description"
                            variant="outlined"
                            density="comfortable"
                            placeholder="Enter category description"
                            :error-messages="form.errors.description"
                            rows="3"
                            rounded="lg"
                        ></v-textarea>
                    </v-form>
                </v-card-text>
                
                <v-card-actions class="pa-4 pt-0 border-t mt-4">
                    <v-spacer></v-spacer>
                    <v-btn
                        variant="tonal"
                        color="grey-darken-1"
                        @click="dialog = false"
                        rounded="lg"
                        class="px-4 text-none"
                    >
                        Cancel
                    </v-btn>
                    <v-btn
                        color="primary"
                        @click="submit"
                        :loading="form.processing"
                        rounded="lg"
                        class="px-4 text-none"
                        variant="flat"
                    >
                        Save Category
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
.search-input :deep(.v-field__outline__start),
.search-input :deep(.v-field__outline__end) {
    border-color: #E0E0E0 !important;
}

.category-card {
    transition: all 0.2s ease;
    border-color: #F0F0F0 !important;
    background-color: #ffffff;
}
.category-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
    transform: translateY(-2px);
}

.icon-box {
    background-color: #F1F6F0;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-btn {
    opacity: 0.7;
    transition: opacity 0.2s;
}
.action-btn:hover {
    opacity: 1;
}

.desc-text {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    height: 40px;
    line-height: 1.4;
}

.text-primary-dark {
    color: #1a1a1a;
}
</style>

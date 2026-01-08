<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ArrowLeft, Plus, Edit, Trash2, Save, X } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';

interface PromptCategory {
    id: number;
    name: string;
    description?: string;
    icon?: string;
    sort_order: number;
    approved_prompts_count: number;
}

interface Props {
    categories: PromptCategory[];
}

const props = defineProps<Props>();

const showCreateForm = ref(false);
const editingCategory = ref<PromptCategory | null>(null);

const createForm = useForm({
    name: '',
    description: '',
    icon: '',
    sort_order: Math.max(...props.categories.map(c => c.sort_order), 0) + 1,
});

const editForm = useForm({
    name: '',
    description: '',
    icon: '',
    sort_order: 0,
});

const startEdit = (category: PromptCategory) => {
    editingCategory.value = category;
    editForm.clearErrors();
    editForm.name = category.name;
    editForm.description = category.description || '';
    editForm.icon = category.icon || '';
    editForm.sort_order = category.sort_order;
};

const cancelEdit = () => {
    editingCategory.value = null;
    editForm.reset();
    editForm.clearErrors();
};

const submitCreate = () => {
    createForm.post(route('admin.prompt-library.categories.store'), {
        onSuccess: () => {
            createForm.reset();
            showCreateForm.value = false;
        },
    });
};

const submitEdit = () => {
    if (!editingCategory.value) return;
    
    editForm.put(route('admin.prompt-library.categories.update', editingCategory.value.id), {
        onSuccess: () => {
            editingCategory.value = null;
            editForm.reset();
        },
    });
};

const deleteCategory = (category: PromptCategory) => {
    if (category.approved_prompts_count > 0) {
        alert('Cannot delete category that contains prompts. Move or delete the prompts first.');
        return;
    }
    
    if (confirm(`Are you sure you want to delete the category "${category.name}"?`)) {
        router.delete(route('admin.prompt-library.categories.destroy', category.id), {
            preserveScroll: true,
        });
    }
};

const cancelCreate = () => {
    showCreateForm.value = false;
    createForm.reset();
    createForm.clearErrors();
};
</script>

<template>
    <AppLayout>
        <Head title="Manage Categories" />

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                    <!-- Header -->
                    <div class="bg-white px-4 py-5 border-b border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <Link
                                    :href="route('admin.prompt-library.index')"
                                    class="text-gray-400 hover:text-gray-600"
                                >
                                    <ArrowLeft class="h-5 w-5" />
                                </Link>
                                <div>
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                                        Manage Categories
                                    </h3>
                                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                        Organize your prompts into categories
                                    </p>
                                </div>
                            </div>
                            <button
                                v-if="!showCreateForm"
                                @click="showCreateForm = true"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                            >
                                <Plus class="h-4 w-4 mr-2" />
                                New Category
                            </button>
                        </div>
                    </div>

                    <!-- Create Form -->
                    <div v-if="showCreateForm" class="border-b border-gray-200 bg-gray-50 p-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-4">Create New Category</h4>
                        <form @submit.prevent="submitCreate" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="createForm.name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    :class="{ 'ring-red-500 focus:ring-red-500': createForm.errors.name }"
                                    placeholder="Category name"
                                />
                                <p v-if="createForm.errors.name" class="mt-1 text-sm text-red-600">
                                    {{ createForm.errors.name }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Icon (Emoji)
                                </label>
                                <input
                                    v-model="createForm.icon"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    placeholder="📁"
                                />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Description
                                </label>
                                <textarea
                                    v-model="createForm.description"
                                    rows="2"
                                    class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    placeholder="Brief description of this category"
                                />
                            </div>
                            <div class="md:col-span-2 flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    @click="cancelCreate"
                                    class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="createForm.processing"
                                    class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
                                >
                                    {{ createForm.processing ? 'Creating...' : 'Create Category' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Categories List -->
                    <div class="divide-y divide-gray-200">
                        <div v-if="categories.length === 0" class="p-6 text-center text-gray-500">
                            <div class="mb-2 text-4xl">📁</div>
                            <div class="text-sm">No categories yet</div>
                            <div class="text-xs text-gray-400 mt-1">Create your first category to get started</div>
                        </div>

                        <div
                            v-for="category in categories"
                            :key="category.id"
                            class="p-6"
                        >
                            <!-- View Mode -->
                            <div v-if="editingCategory?.id !== category.id" class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="text-2xl">{{ category.icon || '📁' }}</div>
                                    <div>
                                        <h4 class="text-lg font-medium text-gray-900">{{ category.name }}</h4>
                                        <p v-if="category.description" class="text-sm text-gray-500">
                                            {{ category.description }}
                                        </p>
                                        <div class="text-xs text-gray-400 mt-1">
                                            {{ category.approved_prompts_count }} prompts • Sort order: {{ category.sort_order }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="startEdit(category)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                        title="Edit category"
                                    >
                                        <Edit class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="deleteCategory(category)"
                                        class="text-red-600 hover:text-red-900"
                                        title="Delete category"
                                        :disabled="category.approved_prompts_count > 0"
                                        :class="{ 'opacity-50 cursor-not-allowed': category.approved_prompts_count > 0 }"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Edit Mode -->
                            <div v-else>
                                <form @submit.prevent="submitEdit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">
                                            Name <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="editForm.name"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                            :class="{ 'ring-red-500 focus:ring-red-500': editForm.errors.name }"
                                        />
                                        <p v-if="editForm.errors.name" class="mt-1 text-sm text-red-600">
                                            {{ editForm.errors.name }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">
                                            Icon (Emoji)
                                        </label>
                                        <input
                                            v-model="editForm.icon"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">
                                            Sort Order
                                        </label>
                                        <input
                                            v-model="editForm.sort_order"
                                            type="number"
                                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        />
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Description
                                        </label>
                                        <textarea
                                            v-model="editForm.description"
                                            rows="2"
                                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        />
                                    </div>
                                    <div class="md:col-span-2 flex items-center justify-end gap-3">
                                        <button
                                            type="button"
                                            @click="cancelEdit"
                                            class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="editForm.processing"
                                            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
                                        >
                                            {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
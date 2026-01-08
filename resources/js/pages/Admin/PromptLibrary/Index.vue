<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Plus, Search, Edit, Trash2, Eye, Settings } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';

interface PromptCategory {
    id: number;
    name: string;
    description?: string;
    icon?: string;
}

interface ApprovedPrompt {
    id: number;
    title: string;
    description?: string;
    content: string;
    tags?: string[];
    is_active: boolean;
    usage_count: number;
    created_at: string;
    category: PromptCategory;
}

interface Props {
    prompts: {
        data: ApprovedPrompt[];
        links: any[];
        meta: any;
    };
    categories: PromptCategory[];
}

defineProps<Props>();

const searchTerm = ref('');
const selectedCategory = ref<number | null>(null);

const handleDelete = (prompt: ApprovedPrompt) => {
    if (confirm(`Are you sure you want to delete "${prompt.title}"?`)) {
        router.delete(route('admin.prompt-library.destroy', prompt.id), {
            preserveScroll: true,
        });
    }
};

const toggleStatus = (prompt: ApprovedPrompt) => {
    router.put(route('admin.prompt-library.update', prompt.id), {
        ...prompt,
        is_active: !prompt.is_active,
    }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Prompt Library Management" />

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                    <!-- Header -->
                    <div class="bg-white px-4 py-5 border-b border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium leading-6 text-gray-900">
                                    Prompt Library Management
                                </h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                    Manage your approved prompt templates and categories
                                </p>
                            </div>
                            <div class="flex gap-3">
                                <Link
                                    :href="route('admin.prompt-library.categories')"
                                    class="inline-flex items-center rounded-md bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500"
                                >
                                    <Settings class="h-4 w-4 mr-2" />
                                    Manage Categories
                                </Link>
                                <Link
                                    :href="route('admin.prompt-library.create')"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                                >
                                    <Plus class="h-4 w-4 mr-2" />
                                    New Prompt
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 sm:px-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="relative flex-1 max-w-xs">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <Search class="h-4 w-4 text-gray-400" />
                                </div>
                                <input
                                    v-model="searchTerm"
                                    type="text"
                                    placeholder="Search prompts..."
                                    class="block w-full rounded-md border-0 py-1.5 pl-9 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                />
                            </div>
                            <div class="flex items-center gap-3">
                                <select
                                    v-model="selectedCategory"
                                    class="block rounded-md border-0 py-1.5 pl-3 pr-8 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                >
                                    <option :value="null">All Categories</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <span class="text-sm text-gray-500">
                                    {{ prompts.meta.total }} prompts
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Prompts Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Title
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Category
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Usage
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tags
                                    </th>
                                    <th class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="prompt in prompts.data" :key="prompt.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ prompt.title }}
                                            </div>
                                            <div v-if="prompt.description" class="text-sm text-gray-500 truncate max-w-xs">
                                                {{ prompt.description }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span v-if="prompt.category.icon" class="mr-2">{{ prompt.category.icon }}</span>
                                            <span class="text-sm text-gray-900">{{ prompt.category.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button
                                            @click="toggleStatus(prompt)"
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer',
                                                prompt.is_active
                                                    ? 'bg-green-100 text-green-800 hover:bg-green-200'
                                                    : 'bg-red-100 text-red-800 hover:bg-red-200'
                                            ]"
                                        >
                                            {{ prompt.is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ prompt.usage_count }} times
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                v-for="tag in (prompt.tags || []).slice(0, 2)"
                                                :key="tag"
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800"
                                            >
                                                {{ tag }}
                                            </span>
                                            <span
                                                v-if="prompt.tags && prompt.tags.length > 2"
                                                class="text-xs text-gray-500"
                                            >
                                                +{{ prompt.tags.length - 2 }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link
                                                :href="route('admin.prompt-library.edit', prompt.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                                title="Edit prompt"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </Link>
                                            <button
                                                @click="handleDelete(prompt)"
                                                class="text-red-600 hover:text-red-900"
                                                title="Delete prompt"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="prompts.meta.last_page > 1" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex justify-between flex-1 sm:hidden">
                                <Link
                                    v-if="prompts.links.prev"
                                    :href="prompts.links.prev"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Previous
                                </Link>
                                <Link
                                    v-if="prompts.links.next"
                                    :href="prompts.links.next"
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Next
                                </Link>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing {{ prompts.meta.from }} to {{ prompts.meta.to }} of {{ prompts.meta.total }} results
                                    </p>
                                </div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <Link
                                        v-for="link in prompts.links"
                                        :key="link.label"
                                        :href="link.url"
                                        v-html="link.label"
                                        :class="[
                                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                            link.active
                                                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                                        ]"
                                    />
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
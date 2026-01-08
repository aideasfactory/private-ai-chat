<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ArrowLeft, Plus, X } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';

interface PromptCategory {
    id: number;
    name: string;
    description?: string;
    icon?: string;
}

interface Props {
    categories: PromptCategory[];
}

const props = defineProps<Props>();

const form = useForm({
    category_id: '',
    title: '',
    content: '',
    description: '',
    tags: [] as string[],
    is_active: true,
});

const newTag = ref('');

const addTag = () => {
    const tag = newTag.value.trim();
    if (tag && !form.tags.includes(tag)) {
        form.tags.push(tag);
        newTag.value = '';
    }
};

const removeTag = (index: number) => {
    form.tags.splice(index, 1);
};

const handleTagKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        addTag();
    }
};

const submit = () => {
    form.post(route('admin.prompt-library.store'), {
        onSuccess: () => {
            // Form will redirect on success
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Create Prompt" />

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
                                        Create New Prompt
                                    </h3>
                                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                        Add a new approved prompt template to your library
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="category"
                                v-model="form.category_id"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                :class="{ 'ring-red-500 focus:ring-red-500': form.errors.category_id }"
                            >
                                <option value="">Select a category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.icon }} {{ category.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">
                                {{ form.errors.category_id }}
                            </p>
                        </div>

                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                :class="{ 'ring-red-500 focus:ring-red-500': form.errors.title }"
                                placeholder="Enter prompt title"
                            />
                            <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                Description
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                :class="{ 'ring-red-500 focus:ring-red-500': form.errors.description }"
                                placeholder="Brief description of when to use this prompt"
                            />
                            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Content -->
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700">
                                Prompt Content <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="content"
                                v-model="form.content"
                                rows="10"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 font-mono"
                                :class="{ 'ring-red-500 focus:ring-red-500': form.errors.content }"
                                placeholder="Enter the prompt content here..."
                            />
                            <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">
                                {{ form.errors.content }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500">
                                Use placeholders like [Client Name], [Policy Type], etc. for dynamic content
                            </p>
                        </div>

                        <!-- Tags -->
                        <div>
                            <label for="tags" class="block text-sm font-medium text-gray-700">
                                Tags
                            </label>
                            <div class="mt-1">
                                <!-- Tag List -->
                                <div v-if="form.tags.length > 0" class="flex flex-wrap gap-2 mb-2">
                                    <span
                                        v-for="(tag, index) in form.tags"
                                        :key="index"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
                                    >
                                        {{ tag }}
                                        <button
                                            type="button"
                                            @click="removeTag(index)"
                                            class="text-indigo-600 hover:text-indigo-800"
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </span>
                                </div>
                                <!-- Tag Input -->
                                <div class="flex gap-2">
                                    <input
                                        v-model="newTag"
                                        type="text"
                                        @keydown="handleTagKeydown"
                                        class="flex-1 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        placeholder="Add tags (press Enter or comma to add)"
                                    />
                                    <button
                                        type="button"
                                        @click="addTag"
                                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                            <p v-if="form.errors.tags" class="mt-1 text-sm text-red-600">
                                {{ form.errors.tags }}
                            </p>
                        </div>

                        <!-- Active Status -->
                        <div class="flex items-center">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                            />
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Active (visible in prompt library)
                            </label>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                            <Link
                                :href="route('admin.prompt-library.index')"
                                class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Creating...' : 'Create Prompt' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
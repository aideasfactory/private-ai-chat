<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { X, Save, Plus } from 'lucide-vue-next';
import { usePromptLibrary } from '@/composables/usePromptLibrary';

interface PromptCategory {
    id: number;
    name: string;
    description?: string;
    icon?: string;
}

interface Props {
    categories: PromptCategory[];
    prompt?: any;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    save: [];
    cancel: [];
}>();

const {
    createPrompt,
    updatePrompt,
} = usePromptLibrary();

const form = ref({
    category_id: '',
    title: '',
    content: '',
    description: '',
    tags: [] as string[],
});

const newTag = ref('');
const loading = ref(false);
const error = ref<string | null>(null);

const isEditing = ref(false);

const addTag = () => {
    const tag = newTag.value.trim();
    if (tag && !form.value.tags.includes(tag)) {
        form.value.tags.push(tag);
        newTag.value = '';
    }
};

const removeTag = (index: number) => {
    form.value.tags.splice(index, 1);
};

const handleTagKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        addTag();
    }
};

const handleSave = async () => {
    if (!form.value.title.trim() || !form.value.content.trim() || !form.value.category_id) {
        error.value = 'Please fill in all required fields';
        return;
    }

    try {
        loading.value = true;
        error.value = null;

        if (isEditing.value && props.prompt) {
            await updatePrompt(props.prompt.id, form.value);
        } else {
            await createPrompt(form.value);
        }

        emit('save');
    } catch (err) {
        error.value = err instanceof Error ? err.message : 'Failed to save prompt';
    } finally {
        loading.value = false;
    }
};

const resetForm = () => {
    form.value = {
        category_id: '',
        title: '',
        content: '',
        description: '',
        tags: [],
    };
    newTag.value = '';
    error.value = null;
};

const populateForm = () => {
    if (props.prompt) {
        isEditing.value = true;
        form.value = {
            category_id: props.prompt.category_id.toString(),
            title: props.prompt.title,
            content: props.prompt.content,
            description: props.prompt.description || '',
            tags: [...(props.prompt.tags || [])],
        };
    } else {
        isEditing.value = false;
        resetForm();
    }
};

onMounted(() => {
    populateForm();
});

watch(() => props.prompt, () => {
    populateForm();
});
</script>

<template>
    <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-gray-900 rounded-lg shadow-xl border border-gray-800 w-full max-w-2xl max-h-[90vh] overflow-hidden">
            <div class="flex h-full flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-800 bg-gray-900/95 p-4">
                    <h3 class="text-lg font-semibold text-gray-100">
                        {{ isEditing ? 'Edit Prompt' : 'Create New Prompt' }}
                    </h3>
                    <button
                        @click="emit('cancel')"
                        class="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-gray-800 transition-colors"
                    >
                        <X class="h-4 w-4 text-gray-400" />
                    </button>
                </div>

                <!-- Form -->
                <div class="flex-1 overflow-y-auto p-6">
                    <form @submit.prevent="handleSave" class="space-y-4">
                        <!-- Error Message -->
                        <div v-if="error" class="rounded-lg bg-red-900/20 border border-red-900/30 px-4 py-3 text-red-400 text-sm">
                            {{ error }}
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Category <span class="text-red-400">*</span>
                            </label>
                            <select
                                v-model="form.category_id"
                                class="w-full rounded-lg bg-gray-800 border border-gray-700 px-3 py-2 text-sm text-gray-100 focus:border-blue-500 focus:outline-none"
                                required
                            >
                                <option value="">Select a category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.icon }} {{ category.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Title <span class="text-red-400">*</span>
                            </label>
                            <input
                                v-model="form.title"
                                type="text"
                                class="w-full rounded-lg bg-gray-800 border border-gray-700 px-3 py-2 text-sm text-gray-100 placeholder-gray-500 focus:border-blue-500 focus:outline-none"
                                placeholder="Enter a clear, descriptive title"
                                required
                            />
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Description
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="2"
                                class="w-full rounded-lg bg-gray-800 border border-gray-700 px-3 py-2 text-sm text-gray-100 placeholder-gray-500 focus:border-blue-500 focus:outline-none"
                                placeholder="Brief description of when to use this prompt"
                            />
                        </div>

                        <!-- Content -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Prompt Content <span class="text-red-400">*</span>
                            </label>
                            <textarea
                                v-model="form.content"
                                rows="8"
                                class="w-full rounded-lg bg-gray-800 border border-gray-700 px-3 py-2 text-sm text-gray-100 placeholder-gray-500 focus:border-blue-500 focus:outline-none font-mono"
                                placeholder="Enter your prompt content here..."
                                required
                            />
                            <p class="mt-1 text-xs text-gray-400">
                                Use placeholders like [Client Name], [Policy Type], etc. for dynamic content
                            </p>
                        </div>

                        <!-- Tags -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Tags
                            </label>
                            <div class="space-y-2">
                                <!-- Existing Tags -->
                                <div v-if="form.tags.length > 0" class="flex flex-wrap gap-2">
                                    <span
                                        v-for="(tag, index) in form.tags"
                                        :key="index"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-blue-600/20 text-blue-400 border border-blue-600/30"
                                    >
                                        {{ tag }}
                                        <button
                                            type="button"
                                            @click="removeTag(index)"
                                            class="text-blue-400 hover:text-blue-300"
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </span>
                                </div>
                                <!-- Add Tag Input -->
                                <div class="flex gap-2">
                                    <input
                                        v-model="newTag"
                                        type="text"
                                        @keydown="handleTagKeydown"
                                        class="flex-1 rounded-lg bg-gray-800 border border-gray-700 px-3 py-2 text-sm text-gray-100 placeholder-gray-500 focus:border-blue-500 focus:outline-none"
                                        placeholder="Add tags (press Enter or comma)"
                                    />
                                    <button
                                        type="button"
                                        @click="addTag"
                                        class="inline-flex items-center px-3 py-2 bg-gray-700 border border-gray-600 text-sm font-medium rounded-lg text-gray-300 hover:bg-gray-600"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 border-t border-gray-800 p-4">
                    <button
                        type="button"
                        @click="emit('cancel')"
                        class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-gray-100 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="handleSave"
                        :disabled="loading || !form.title.trim() || !form.content.trim() || !form.category_id"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <Save class="h-4 w-4" />
                        {{ loading ? 'Saving...' : isEditing ? 'Update Prompt' : 'Create Prompt' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
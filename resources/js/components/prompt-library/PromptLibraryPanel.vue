<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Search, X, Book, Tag, Plus, Edit, Trash2, User, Users } from 'lucide-vue-next';
import { usePromptLibrary } from '@/composables/usePromptLibrary';
import PromptForm from './PromptForm.vue';

export interface PromptLibraryPanelProps {
    isOpen?: boolean;
}

const props = withDefaults(defineProps<PromptLibraryPanelProps>(), {
    isOpen: false,
});

const emit = defineEmits<{
    close: [];
    usePrompt: [content: string, title: string];
}>();

const {
    categories,
    prompts,
    loading,
    error,
    fetchCategories,
    fetchPrompts,
    fetchMyPrompts,
    usePrompt,
    createPrompt,
    updatePrompt,
    deletePrompt,
} = usePromptLibrary();

const searchTerm = ref('');
const selectedCategoryId = ref<number | null>(null);
const currentView = ref<'all' | 'mine'>('all');
const showPromptForm = ref(false);
const editingPrompt = ref<any | null>(null);

const filteredPrompts = computed(() => {
    let filtered = prompts.value;

    if (selectedCategoryId.value) {
        filtered = filtered.filter(prompt => prompt.category_id === selectedCategoryId.value);
    }

    if (searchTerm.value) {
        const search = searchTerm.value.toLowerCase();
        filtered = filtered.filter(prompt => 
            prompt.title.toLowerCase().includes(search) ||
            prompt.description?.toLowerCase().includes(search) ||
            prompt.content.toLowerCase().includes(search) ||
            (prompt.tags && prompt.tags.some((tag: string) => tag.toLowerCase().includes(search)))
        );
    }

    return filtered;
});

const handleUsePrompt = async (prompt: any) => {
    try {
        await usePrompt(prompt.id);
        emit('usePrompt', prompt.content, prompt.title);
        emit('close');
    } catch (err) {
        console.error('Failed to use prompt:', err);
    }
};

const handleCategorySelect = (categoryId: number | null) => {
    selectedCategoryId.value = categoryId;
};

const clearSearch = () => {
    searchTerm.value = '';
};

const handleViewChange = async (view: 'all' | 'mine') => {
    currentView.value = view;
    selectedCategoryId.value = null;
    searchTerm.value = '';
    
    if (view === 'all') {
        await fetchPrompts();
    } else {
        await fetchMyPrompts();
    }
};

const handleCreatePrompt = () => {
    editingPrompt.value = null;
    showPromptForm.value = true;
};

const handleEditPrompt = (prompt: any) => {
    editingPrompt.value = prompt;
    showPromptForm.value = true;
};

const handleDeletePrompt = async (prompt: any) => {
    if (confirm(`Are you sure you want to delete "${prompt.title}"?`)) {
        try {
            await deletePrompt(prompt.id);
        } catch (err) {
            console.error('Failed to delete prompt:', err);
        }
    }
};

const handlePromptSaved = async () => {
    showPromptForm.value = false;
    editingPrompt.value = null;
    // Refresh the current view
    if (currentView.value === 'all') {
        await fetchPrompts();
    } else {
        await fetchMyPrompts();
    }
};

const handlePromptFormCancel = () => {
    showPromptForm.value = false;
    editingPrompt.value = null;
};

onMounted(async () => {
    await fetchCategories();
    await fetchPrompts();
});
</script>

<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
        @click="emit('close')"
    >
        <div
            class="fixed right-0 top-0 h-full w-full max-w-md bg-gray-900 shadow-xl border-l border-gray-800"
            @click.stop
        >
            <div class="flex h-full flex-col">
                <!-- Header -->
                <div class="border-b border-gray-800 bg-gray-900/95 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <Book class="h-5 w-5 text-blue-400" />
                            <h2 class="text-lg font-semibold text-gray-100">Prompt Library</h2>
                        </div>
                        <button
                            @click="emit('close')"
                            class="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-gray-800 transition-colors"
                        >
                            <X class="h-4 w-4 text-gray-400" />
                        </button>
                    </div>
                    
                    <!-- View Switcher -->
                    <div class="flex items-center justify-between">
                        <div class="flex gap-2">
                            <button
                                @click="handleViewChange('all')"
                                :class="[
                                    'flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm transition-colors',
                                    currentView === 'all'
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-800 text-gray-300 hover:bg-gray-700'
                                ]"
                            >
                                <Users class="h-4 w-4" />
                                All Prompts
                            </button>
                            <button
                                @click="handleViewChange('mine')"
                                :class="[
                                    'flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm transition-colors',
                                    currentView === 'mine'
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-800 text-gray-300 hover:bg-gray-700'
                                ]"
                            >
                                <User class="h-4 w-4" />
                                My Prompts
                            </button>
                        </div>
                        <button
                            @click="handleCreatePrompt"
                            class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm bg-green-600 text-white hover:bg-green-700 transition-colors"
                        >
                            <Plus class="h-4 w-4" />
                            New
                        </button>
                    </div>
                </div>

                <!-- Search -->
                <div class="border-b border-gray-800 p-4">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" />
                        <input
                            v-model="searchTerm"
                            type="text"
                            placeholder="Search prompts..."
                            class="w-full rounded-lg bg-gray-800 pl-9 pr-9 py-2 text-sm text-gray-100 placeholder-gray-500 border border-gray-700 focus:border-gray-600 focus:outline-none"
                        />
                        <button
                            v-if="searchTerm"
                            @click="clearSearch"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <!-- Categories -->
                <div class="border-b border-gray-800 p-4">
                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="handleCategorySelect(null)"
                            :class="[
                                'rounded-full px-3 py-1 text-xs transition-colors',
                                selectedCategoryId === null
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-800 text-gray-300 hover:bg-gray-700'
                            ]"
                        >
                            All
                        </button>
                        <button
                            v-for="category in categories"
                            :key="category.id"
                            @click="handleCategorySelect(category.id)"
                            :class="[
                                'rounded-full px-3 py-1 text-xs transition-colors flex items-center gap-1',
                                selectedCategoryId === category.id
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-800 text-gray-300 hover:bg-gray-700'
                            ]"
                        >
                            <span v-if="category.icon">{{ category.icon }}</span>
                            {{ category.name }}
                        </button>
                    </div>
                </div>

                <!-- Prompts List -->
                <div class="flex-1 overflow-y-auto">
                    <div v-if="loading" class="p-4 text-center text-gray-500">
                        Loading prompts...
                    </div>

                    <div v-else-if="error" class="p-4 text-center text-red-400">
                        {{ error }}
                    </div>

                    <div v-else-if="filteredPrompts.length === 0" class="p-4 text-center text-gray-500">
                        <div class="mb-2">📝</div>
                        <div class="text-sm">No prompts found</div>
                        <div class="text-xs text-gray-600 mt-1">
                            {{ searchTerm ? 'Try adjusting your search' : 'No prompts in this category' }}
                        </div>
                    </div>

                    <div v-else class="divide-y divide-gray-800">
                        <div
                            v-for="prompt in filteredPrompts"
                            :key="prompt.id"
                            class="group p-4 hover:bg-gray-800/50 transition-colors"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div 
                                    class="flex-1 min-w-0 cursor-pointer"
                                    @click="handleUsePrompt(prompt)"
                                >
                                    <h3 class="font-medium text-gray-100 text-sm group-hover:text-blue-400 transition-colors">
                                        {{ prompt.title }}
                                    </h3>
                                    <p v-if="prompt.description" class="text-xs text-gray-400 mt-1 line-clamp-2">
                                        {{ prompt.description }}
                                    </p>
                                    <div v-if="prompt.tags && prompt.tags.length > 0" class="flex flex-wrap gap-1 mt-2">
                                        <span
                                            v-for="tag in prompt.tags.slice(0, 3)"
                                            :key="tag"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-800 text-xs text-gray-400"
                                        >
                                            <Tag class="h-2.5 w-2.5" />
                                            {{ tag }}
                                        </span>
                                        <span
                                            v-if="prompt.tags.length > 3"
                                            class="text-xs text-gray-500"
                                        >
                                            +{{ prompt.tags.length - 3 }}
                                        </span>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-600 flex items-center justify-between">
                                        <span>Used {{ prompt.usage_count || 0 }} times</span>
                                        <div class="flex items-center text-xs text-gray-600">
                                            <span v-if="prompt.category?.icon" class="mr-1">{{ prompt.category.icon }}</span>
                                            {{ prompt.category?.name }}
                                        </div>
                                    </div>
                                </div>
                                <!-- Action buttons for user's own prompts -->
                                <div 
                                    v-if="currentView === 'mine'"
                                    class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
                                >
                                    <button
                                        @click.stop="handleEditPrompt(prompt)"
                                        class="flex h-7 w-7 items-center justify-center rounded-lg hover:bg-gray-700 transition-colors"
                                        title="Edit prompt"
                                    >
                                        <Edit class="h-3 w-3 text-gray-400 hover:text-gray-300" />
                                    </button>
                                    <button
                                        @click.stop="handleDeletePrompt(prompt)"
                                        class="flex h-7 w-7 items-center justify-center rounded-lg hover:bg-gray-700 transition-colors"
                                        title="Delete prompt"
                                    >
                                        <Trash2 class="h-3 w-3 text-red-400 hover:text-red-300" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-800 p-4 text-center">
                    <div class="text-xs text-gray-500">
                        Click any prompt to insert it into your message
                    </div>
                </div>
            </div>
        </div>

        <!-- Prompt Form Modal -->
        <PromptForm
            v-if="showPromptForm"
            :categories="categories"
            :prompt="editingPrompt"
            @save="handlePromptSaved"
            @cancel="handlePromptFormCancel"
        />
    </div>
</template>

<style scoped>
.line-clamp-2 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
</style>
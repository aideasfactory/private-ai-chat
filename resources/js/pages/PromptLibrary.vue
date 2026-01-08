<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Search, X, Book, Tag, Plus, Edit, Trash2, User, Users, ArrowLeft, Menu, History } from 'lucide-vue-next';
import { usePromptLibrary } from '@/composables/usePromptLibrary';
import { useChat } from '@/composables/useChat';
import PromptForm from '@/components/prompt-library/PromptForm.vue';
import ChatSidebar from '@/components/chat/ChatSidebar.vue';

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

// Chat sidebar state and functionality
const sidebarOpen = ref(true);
const {
    chats,
    fetchChats,
    createChat,
} = useChat();

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

const handleUsePrompt = async (prompt: any) => {
    try {
        await usePrompt(prompt.id);
        // Create a new empty chat
        const newChat = await createChat();
        // Navigate to dashboard with the new chat and the prompt content in the URL
        const encodedPrompt = encodeURIComponent(prompt.content);
        window.location.href = `/dashboard?chat=${newChat.id}&prompt=${encodedPrompt}`;
    } catch (err) {
        console.error('Failed to use prompt:', err);
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

// Chat sidebar handlers
const handleSelectChat = (chatId: number) => {
    // Navigate to dashboard with the selected chat
    window.location.href = `/dashboard?chat=${chatId}`;
};

const handleNewChat = async () => {
    await createChat();
    // Navigate back to dashboard
    window.location.href = '/dashboard';
};

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

    if (days === 0) {
        const hours = Math.floor(diff / (1000 * 60 * 60));
        if (hours === 0) {
            const minutes = Math.floor(diff / (1000 * 60));
            return minutes === 0 ? 'Just now' : `${minutes}m ago`;
        }
        return `${hours}h ago`;
    } else if (days === 1) {
        return 'Yesterday';
    } else if (days < 7) {
        return `${days} days ago`;
    } else {
        return date.toLocaleDateString();
    }
};

onMounted(async () => {
    await fetchCategories();
    await fetchPrompts();
    await fetchChats();
});
</script>

<template>
    <Head title="Prompt Library" />
    
    <div class="flex h-screen bg-gray-950 text-gray-100">
        <!-- Custom Sidebar for Prompt Library -->
        <div
            :class="[
                'flex h-full flex-col bg-gray-900 transition-all duration-300',
                'border-r border-gray-800',
                sidebarOpen ? 'w-80' : 'w-0 md:w-16',
            ]"
        >
            <div
                :class="[
                    'flex items-center justify-between p-4',
                    !sidebarOpen && 'md:justify-center',
                ]"
            >
                <button
                    @click="toggleSidebar"
                    class="flex h-10 w-10 items-center justify-center rounded-lg hover:bg-gray-800 transition-colors md:hidden"
                >
                    <X v-if="sidebarOpen" class="h-5 w-5 text-gray-400" />
                    <Menu v-else class="h-5 w-5 text-gray-400" />
                </button>

                <button
                    v-if="sidebarOpen"
                    @click="handleNewChat"
                    class="flex flex-1 items-center gap-2 ml-2 rounded-lg border border-gray-700 px-3 py-2 text-sm font-medium text-gray-200 transition-colors hover:bg-gray-800"
                >
                    <Plus class="h-4 w-4" />
                    New Chat
                </button>

                <button
                    v-if="!sidebarOpen"
                    @click="toggleSidebar"
                    class="hidden md:flex h-10 w-10 items-center justify-center rounded-lg hover:bg-gray-800 transition-colors"
                >
                    <Menu class="h-5 w-5 text-gray-400" />
                </button>
            </div>

            <div
                v-if="sidebarOpen"
                class="flex-1 overflow-y-auto px-2"
            >
                <!-- Navigation Section -->
                <div class="space-y-1 mb-4">
                    <div class="w-full rounded-lg p-3 text-left bg-gray-800/70 text-gray-300">
                        <div class="flex items-center gap-3">
                            <Book class="h-5 w-5 text-blue-400" />
                            <div class="flex-1">
                                <div class="font-medium text-sm">Prompt Library</div>
                                <div class="text-xs opacity-70">Browse & manage prompts</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chats Section -->
                <div class="border-t border-gray-800 pt-4">
                    <div class="flex items-center gap-2 px-3 pb-2">
                        <History class="h-4 w-4 text-gray-500" />
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Recent Chats</span>
                    </div>
                    <div class="space-y-1 pb-4">
                        <button
                            v-for="chat in chats"
                            :key="chat.id"
                            @click="handleSelectChat(chat.id)"
                            class="group relative w-full rounded-lg p-3 text-left transition-colors hover:bg-gray-800/50 text-gray-400"
                        >
                            <div class="mb-1 font-medium text-sm line-clamp-1">
                                {{ chat.title }}
                            </div>
                            <div
                                v-if="chat.latest_message"
                                class="text-xs line-clamp-2 opacity-70"
                            >
                                {{ chat.latest_message.content }}
                            </div>
                            <div class="mt-1 text-xs opacity-50">
                                {{ formatDate(chat.last_message_at) }}
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <div
                v-if="!sidebarOpen"
                class="hidden md:flex flex-1 flex-col items-center gap-2 overflow-y-auto px-2 py-2"
            >
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-800 transition-colors">
                    <Book class="h-5 w-5 text-blue-400" />
                </div>
                <button
                    @click="handleNewChat"
                    class="flex h-10 w-10 items-center justify-center rounded-lg hover:bg-gray-800 transition-colors"
                >
                    <Plus class="h-5 w-5 text-gray-400" />
                </button>
                <div class="w-full border-t border-gray-800 mt-2 pt-2">
                    <div class="space-y-2">
                        <button
                            v-for="(chat, index) in chats.slice(0, 5)"
                            :key="chat.id"
                            @click="handleSelectChat(chat.id)"
                            class="flex h-10 w-10 items-center justify-center rounded-lg transition-colors hover:bg-gray-800 text-gray-400"
                            :title="chat.title"
                        >
                            <span class="text-xs font-medium">{{ index + 1 }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-1 flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-800 bg-gray-900/50 px-4 py-3">
                <div class="flex items-center gap-4">
                    <Link 
                        href="/dashboard" 
                        class="text-gray-400 hover:text-gray-300 transition-colors"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                    <div class="flex items-center gap-3">
                        <Book class="h-6 w-6 text-blue-400" />
                        <div>
                            <h1 class="text-lg font-semibold text-gray-100">Prompt Library</h1>
                            <p class="text-xs text-gray-400">Browse and manage your prompt templates</p>
                        </div>
                    </div>
                </div>
                
                <button
                    @click="handleCreatePrompt"
                    class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <Plus class="h-4 w-4" />
                    New Prompt
                </button>
            </div>

            <!-- Main Content -->
            <div class="flex-1 overflow-y-auto px-4 py-6">
                <div class="mx-auto max-w-6xl">

                    <!-- Controls -->
                    <div class="mb-6 space-y-4">
                        <!-- View Switcher & Search -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex gap-2">
                                <button
                                    @click="handleViewChange('all')"
                                    :class="[
                                        'flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                                        currentView === 'all'
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-gray-800 text-gray-300 hover:bg-gray-700'
                                    ]"
                                >
                                    <Users class="h-4 w-4" />
                                    All Prompts ({{ currentView === 'all' ? filteredPrompts.length : 0 }})
                                </button>
                                <button
                                    @click="handleViewChange('mine')"
                                    :class="[
                                        'flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                                        currentView === 'mine'
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-gray-800 text-gray-300 hover:bg-gray-700'
                                    ]"
                                >
                                    <User class="h-4 w-4" />
                                    My Prompts ({{ currentView === 'mine' ? filteredPrompts.length : 0 }})
                                </button>
                            </div>
                            
                            <div class="relative max-w-md">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" />
                                <input
                                    v-model="searchTerm"
                                    type="text"
                                    placeholder="Search prompts..."
                                    class="w-full rounded-lg bg-gray-800 border border-gray-700 pl-9 pr-9 py-2 text-sm text-gray-100 placeholder-gray-500 focus:border-blue-500 focus:outline-none"
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

                        <!-- Category Filter -->
                        <div class="flex flex-wrap gap-2">
                            <button
                                @click="handleCategorySelect(null)"
                                :class="[
                                    'rounded-full px-3 py-1 text-sm transition-colors',
                                    selectedCategoryId === null
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-800 text-gray-300 hover:bg-gray-700'
                                ]"
                            >
                                All Categories
                            </button>
                            <button
                                v-for="category in categories"
                                :key="category.id"
                                @click="handleCategorySelect(category.id)"
                                :class="[
                                    'rounded-full px-3 py-1 text-sm transition-colors flex items-center gap-1',
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

                    <!-- Content -->
                    <div class="bg-gray-800 rounded-lg border border-gray-700">
                        <div v-if="loading" class="p-8 text-center text-gray-500">
                            Loading prompts...
                        </div>

                        <div v-else-if="error" class="p-8 text-center text-red-400">
                            {{ error }}
                        </div>

                        <div v-else-if="filteredPrompts.length === 0" class="p-8 text-center text-gray-500">
                            <div class="mb-4 text-5xl">📝</div>
                            <div class="text-lg font-medium">No prompts found</div>
                            <div class="text-sm text-gray-400 mt-1">
                                {{ searchTerm ? 'Try adjusting your search' : 'No prompts in this category' }}
                            </div>
                        </div>

                        <div v-else class="divide-y divide-gray-700">
                            <div
                                v-for="prompt in filteredPrompts"
                                :key="prompt.id"
                                class="group p-6 hover:bg-gray-750 transition-colors"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="font-semibold text-lg text-gray-100">
                                                {{ prompt.title }}
                                            </h3>
                                            <div class="flex items-center text-sm text-gray-400">
                                                <span v-if="prompt.category?.icon" class="mr-1">{{ prompt.category.icon }}</span>
                                                {{ prompt.category?.name }}
                                            </div>
                                        </div>
                                        
                                        <p v-if="prompt.description" class="text-gray-400 mb-3">
                                            {{ prompt.description }}
                                        </p>
                                        
                                        <div class="text-sm text-gray-300 mb-4 bg-gray-900 p-3 rounded-lg font-mono max-h-32 overflow-hidden">
                                            {{ prompt.content.substring(0, 200) }}{{ prompt.content.length > 200 ? '...' : '' }}
                                        </div>
                                        
                                        <div class="flex items-center gap-4">
                                            <div v-if="prompt.tags && prompt.tags.length > 0" class="flex flex-wrap gap-1">
                                                <span
                                                    v-for="tag in prompt.tags.slice(0, 3)"
                                                    :key="tag"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-900/30 text-blue-300 text-xs"
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
                                            <div class="text-xs text-gray-500">
                                                Used {{ prompt.usage_count || 0 }} times
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="handleUsePrompt(prompt)"
                                            class="px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                                        >
                                            Use Prompt
                                        </button>
                                        <div 
                                            v-if="currentView === 'mine'"
                                            class="flex items-center gap-1"
                                        >
                                            <button
                                                @click="handleEditPrompt(prompt)"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-gray-700 transition-colors"
                                                title="Edit prompt"
                                            >
                                                <Edit class="h-4 w-4 text-gray-400" />
                                            </button>
                                            <button
                                                @click="handleDeletePrompt(prompt)"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-gray-700 transition-colors"
                                                title="Delete prompt"
                                            >
                                                <Trash2 class="h-4 w-4 text-red-400" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
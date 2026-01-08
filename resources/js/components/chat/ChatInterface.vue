<script setup lang="ts">
import { ref, nextTick, onMounted, computed, watch } from 'vue';
import ChatSidebar from './ChatSidebar.vue';
import ChatMessage from './ChatMessage.vue';
import ChatInput from './ChatInput.vue';
import { Menu, MoreVertical, Trash2, Edit3 } from 'lucide-vue-next';
import { useChat } from '@/composables/useChat';
import Toast from '@/components/ui/Toast.vue';

const sidebarOpen = ref(true);
const messagesContainer = ref<HTMLDivElement | null>(null);
const attachedFiles = ref<File[]>([]);
const toasts = ref<Array<{ id: number; message: string; type: 'success' | 'error' | 'info' }>>([]);

const {
    chats,
    currentChat,
    loading,
    sending,
    error,
    fetchChats,
    fetchChat,
    createChat,
    updateChat,
    deleteChat,
    sendMessage,
    uploadDocument,
} = useChat();

const messages = computed(() => {
    return currentChat.value?.messages || [];
});

const selectedChatId = computed(() => currentChat.value?.id);

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const handleSelectChat = async (chatId: number) => {
    await fetchChat(chatId);
    scrollToBottom();
};

const handleNewChat = async () => {
    await createChat();
};

const handleSendMessage = async (content: string) => {
    if (!content.trim() && attachedFiles.value.length === 0) return;
    
    let chatId = currentChat.value?.id;
    
    // Create a new chat if none exists
    if (!chatId) {
        const newChat = await createChat('New Chat', content);
        chatId = newChat.id;
        return; // The initial message was already sent during chat creation
    }
    
    // Clear input immediately for better UX
    const filesSnapshot = [...attachedFiles.value];
    attachedFiles.value = [];
    
    try {
        await sendMessage(
            chatId,
            content,
            filesSnapshot.length > 0 ? filesSnapshot : undefined
        );
        scrollToBottom();
    } catch (err) {
        console.error('Failed to send message:', err);
        // Restore files if there was an error
        attachedFiles.value = filesSnapshot;
    }
};

const handleFileSelect = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files) {
        attachedFiles.value = Array.from(input.files);
    }
};

const removeFile = (index: number) => {
    attachedFiles.value.splice(index, 1);
};

const handleDeleteChat = async () => {
    if (!currentChat.value) return;
    
    if (confirm('Are you sure you want to delete this chat?')) {
        await deleteChat(currentChat.value.id);
        if (chats.value.length > 0) {
            await handleSelectChat(chats.value[0].id);
        } else {
            currentChat.value = null;
        }
    }
};

const handleRenameChat = async () => {
    if (!currentChat.value) return;
    
    const newTitle = prompt('Enter new chat title:', currentChat.value.title);
    if (newTitle && newTitle !== currentChat.value.title) {
        await updateChat(currentChat.value.id, { title: newTitle });
    }
};

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

onMounted(async () => {
    await fetchChats();
    if (chats.value.length > 0) {
        await handleSelectChat(chats.value[0].id);
    }
});

// Watch for changes in messages to auto-scroll
watch(messages, () => {
    nextTick(() => scrollToBottom());
}, { deep: true });
</script>

<template>
    <div class="flex h-screen bg-gray-950 text-gray-100">
        <ChatSidebar
            :chats="chats"
            :selected-chat-id="selectedChatId"
            :is-open="sidebarOpen"
            @select-chat="handleSelectChat"
            @new-chat="handleNewChat"
            @toggle-sidebar="toggleSidebar"
        />

        <div class="flex flex-1 flex-col">
            <div
                class="flex items-center justify-between border-b border-gray-800 bg-gray-900/50 px-4 py-3"
            >
                <div class="flex items-center gap-3">
                    <button
                        @click="toggleSidebar"
                        class="flex h-9 w-9 items-center justify-center rounded-lg hover:bg-gray-800 transition-colors md:hidden"
                    >
                        <Menu class="h-5 w-5 text-gray-400" />
                    </button>
                    <h1 class="text-lg font-semibold">
                        {{ currentChat?.title || 'New Chat' }}
                    </h1>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="handleRenameChat"
                        class="flex h-9 w-9 items-center justify-center rounded-lg hover:bg-gray-800 transition-colors"
                        title="Rename chat"
                    >
                        <Edit3 class="h-5 w-5 text-gray-400" />
                    </button>
                    <button
                        @click="handleDeleteChat"
                        class="flex h-9 w-9 items-center justify-center rounded-lg hover:bg-gray-800 transition-colors"
                        title="Delete chat"
                    >
                        <Trash2 class="h-5 w-5 text-gray-400" />
                    </button>
                </div>
            </div>

            <div
                ref="messagesContainer"
                class="flex-1 overflow-y-auto px-4 py-6"
            >
                <div
                    v-if="messages.length === 0"
                    class="flex h-full items-center justify-center"
                >
                    <div class="text-center">
                        <div class="mb-4 text-5xl">💬</div>
                        <h2 class="mb-2 text-2xl font-semibold">
                            Start a new conversation
                        </h2>
                        <p class="text-gray-400">
                            Ask me anything or choose a conversation from the sidebar
                        </p>
                    </div>
                </div>

                <div
                    v-else
                    class="mx-auto max-w-4xl space-y-6"
                >
                    <ChatMessage
                        v-for="message in messages"
                        :key="message.id"
                        :message="{
                            id: message.id,
                            content: message.content,
                            role: message.role,
                            timestamp: new Date(message.created_at),
                        }"
                    />

                </div>
                
                <!-- Error message display -->
                <div
                    v-if="error"
                    class="mx-auto max-w-4xl"
                >
                    <div class="rounded-lg bg-red-900/20 border border-red-900/30 px-4 py-3 text-red-400 text-sm">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                            {{ error }}
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <!-- Attached files preview -->
                <div v-if="attachedFiles.length > 0" class="border-t border-gray-800 bg-gray-900/50 px-4 py-2">
                    <div class="mx-auto max-w-4xl">
                        <div class="flex flex-wrap gap-2">
                            <div
                                v-for="(file, index) in attachedFiles"
                                :key="index"
                                class="flex items-center gap-2 rounded-lg bg-gray-800 px-3 py-1.5 text-sm text-gray-300"
                            >
                                <span>{{ file.name }}</span>
                                <button
                                    @click="removeFile(index)"
                                    class="text-gray-500 hover:text-gray-300"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <ChatInput
                    :disabled="sending || loading || !currentChat"
                    :attached-files="attachedFiles"
                    :placeholder="currentChat ? 'Send a message...' : 'Select a chat to start messaging'"
                    @send-message="handleSendMessage"
                    @file-select="handleFileSelect"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes bounce {
    0%, 60%, 100% {
        transform: translateY(0);
    }
    30% {
        transform: translateY(-10px);
    }
}

.animation-delay-0 {
    animation-delay: 0ms;
}

.animation-delay-200 {
    animation-delay: 200ms;
}

.animation-delay-400 {
    animation-delay: 400ms;
}
</style>
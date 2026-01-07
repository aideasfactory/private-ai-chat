<script setup lang="ts">
import { ref, nextTick, onMounted } from 'vue';
import ChatSidebar from './ChatSidebar.vue';
import ChatMessage from './ChatMessage.vue';
import ChatInput from './ChatInput.vue';
import { Menu, MoreVertical } from 'lucide-vue-next';

interface Message {
    id: number;
    content: string;
    role: 'user' | 'assistant';
    timestamp: Date;
}

interface Chat {
    id: number;
    title: string;
    messages: Message[];
}

const sidebarOpen = ref(true);
const selectedChatId = ref<number>(1);
const isLoading = ref(false);
const messagesContainer = ref<HTMLDivElement | null>(null);

const mockChats: Chat[] = [
    {
        id: 1,
        title: 'Project Planning Discussion',
        messages: [
            {
                id: 1,
                content: 'Can you help me plan out a new SaaS project? I want to build a task management application.',
                role: 'user',
                timestamp: new Date(Date.now() - 1000 * 60 * 30),
            },
            {
                id: 2,
                content: `I'd be happy to help you plan your SaaS task management application! Let's break this down into key components:

**1. Core Features to Consider:**
- User authentication and authorization
- Task creation, editing, and deletion
- Project/workspace organization
- Team collaboration features
- Task assignments and due dates
- Priority levels and tags
- Comments and activity feeds
- File attachments
- Search and filtering

**2. Technical Architecture:**
- Frontend: Consider React/Vue.js with TypeScript
- Backend: Node.js/Express or Laravel
- Database: PostgreSQL for relational data
- Real-time updates: WebSockets or Server-Sent Events
- File storage: AWS S3 or similar
- Authentication: JWT tokens or OAuth

Would you like me to elaborate on any specific aspect?`,
                role: 'assistant',
                timestamp: new Date(Date.now() - 1000 * 60 * 28),
            },
            {
                id: 3,
                content: 'That sounds great! Can you help me with the database schema design?',
                role: 'user',
                timestamp: new Date(Date.now() - 1000 * 60 * 25),
            },
            {
                id: 4,
                content: `Absolutely! Here's a suggested database schema for your task management application:

**Core Tables:**

1. **users**
   - id (UUID, primary key)
   - email (unique)
   - name
   - password_hash
   - created_at
   - updated_at

2. **workspaces**
   - id (UUID, primary key)
   - name
   - description
   - owner_id (foreign key to users)
   - created_at
   - updated_at

3. **projects**
   - id (UUID, primary key)
   - workspace_id (foreign key)
   - name
   - description
   - color
   - created_at
   - updated_at

4. **tasks**
   - id (UUID, primary key)
   - project_id (foreign key)
   - title
   - description
   - status (enum: todo, in_progress, done)
   - priority (enum: low, medium, high, critical)
   - assignee_id (foreign key to users)
   - due_date
   - created_by (foreign key to users)
   - created_at
   - updated_at

This structure provides a solid foundation. Would you like me to add more tables for advanced features?`,
                role: 'assistant',
                timestamp: new Date(Date.now() - 1000 * 60 * 20),
            },
        ],
    },
    {
        id: 2,
        title: 'Code Review Assistant',
        messages: [
            {
                id: 5,
                content: 'Can you review this React component for best practices?',
                role: 'user',
                timestamp: new Date(Date.now() - 1000 * 60 * 60 * 2),
            },
            {
                id: 6,
                content: 'I\'d be happy to review your React component! However, I don\'t see any code attached. Could you please share the component code you\'d like me to review?',
                role: 'assistant',
                timestamp: new Date(Date.now() - 1000 * 60 * 60 * 2 + 1000 * 30),
            },
        ],
    },
];

const currentChat = ref<Chat | null>(mockChats[0]);
const messages = ref<Message[]>(mockChats[0].messages);

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const handleSelectChat = (chatId: number) => {
    selectedChatId.value = chatId;
    const chat = mockChats.find((c) => c.id === chatId);
    if (chat) {
        currentChat.value = chat;
        messages.value = chat.messages;
        scrollToBottom();
    }
};

const handleNewChat = () => {
    selectedChatId.value = undefined;
    currentChat.value = null;
    messages.value = [];
};

const handleSendMessage = async (content: string) => {
    if (!content.trim()) return;

    const userMessage: Message = {
        id: Date.now(),
        content,
        role: 'user',
        timestamp: new Date(),
    };

    messages.value.push(userMessage);
    scrollToBottom();

    isLoading.value = true;

    setTimeout(() => {
        const assistantMessage: Message = {
            id: Date.now() + 1,
            content: 'This is a mock response. In a real application, this would be generated by your AI backend.',
            role: 'assistant',
            timestamp: new Date(),
        };
        messages.value.push(assistantMessage);
        isLoading.value = false;
        scrollToBottom();
    }, 1500);
};

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

onMounted(() => {
    scrollToBottom();
});
</script>

<template>
    <div class="flex h-screen bg-gray-950 text-gray-100">
        <ChatSidebar
            :chats="[]"
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
                <button
                    class="flex h-9 w-9 items-center justify-center rounded-lg hover:bg-gray-800 transition-colors"
                >
                    <MoreVertical class="h-5 w-5 text-gray-400" />
                </button>
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
                        :message="message"
                    />

                    <div
                        v-if="isLoading"
                        class="flex gap-3"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-700 text-gray-200"
                        >
                            <span class="text-sm font-medium">AI</span>
                        </div>
                        <div
                            class="rounded-2xl bg-gray-700/50 px-4 py-2 text-gray-100"
                        >
                            <div class="flex gap-1">
                                <span class="animate-bounce animation-delay-0">.</span>
                                <span class="animate-bounce animation-delay-200">.</span>
                                <span class="animate-bounce animation-delay-400">.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <ChatInput
                :disabled="isLoading"
                @send-message="handleSendMessage"
            />
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
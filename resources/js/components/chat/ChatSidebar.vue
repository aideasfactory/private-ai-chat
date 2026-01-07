<script setup lang="ts">
import { ref } from 'vue';
import { Menu, Plus, X } from 'lucide-vue-next';

export interface Chat {
    id: number;
    title: string;
    last_message_at: string;
    latest_message?: {
        content: string;
    };
}

export interface ChatSidebarProps {
    chats: Chat[];
    selectedChatId?: number;
    isOpen: boolean;
}

const props = defineProps<ChatSidebarProps>();

const emit = defineEmits<{
    selectChat: [id: number];
    newChat: [];
    toggleSidebar: [];
}>();


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
</script>

<template>
    <div
        :class="[
            'flex h-full flex-col bg-gray-900 transition-all duration-300',
            'border-r border-gray-800',
            isOpen ? 'w-80' : 'w-0 md:w-16',
        ]"
    >
        <div
            :class="[
                'flex items-center justify-between p-4',
                !isOpen && 'md:justify-center',
            ]"
        >
            <button
                @click="emit('toggleSidebar')"
                class="flex h-10 w-10 items-center justify-center rounded-lg hover:bg-gray-800 transition-colors md:hidden"
            >
                <X v-if="isOpen" class="h-5 w-5 text-gray-400" />
                <Menu v-else class="h-5 w-5 text-gray-400" />
            </button>

            <button
                v-if="isOpen"
                @click="emit('newChat')"
                class="flex flex-1 items-center gap-2 ml-2 rounded-lg border border-gray-700 px-3 py-2 text-sm font-medium text-gray-200 transition-colors hover:bg-gray-800"
            >
                <Plus class="h-4 w-4" />
                New Chat
            </button>

            <button
                v-if="!isOpen"
                @click="emit('toggleSidebar')"
                class="hidden md:flex h-10 w-10 items-center justify-center rounded-lg hover:bg-gray-800 transition-colors"
            >
                <Menu class="h-5 w-5 text-gray-400" />
            </button>
        </div>

        <div
            v-if="isOpen"
            class="flex-1 overflow-y-auto px-2"
        >
            <div class="space-y-1 pb-4">
                <button
                    v-for="chat in props.chats"
                    :key="chat.id"
                    @click="emit('selectChat', chat.id)"
                    :class="[
                        'group relative w-full rounded-lg p-3 text-left transition-colors',
                        props.selectedChatId === chat.id
                            ? 'bg-gray-800 text-gray-100'
                            : 'hover:bg-gray-800/50 text-gray-400',
                    ]"
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

        <div
            v-if="!isOpen"
            class="hidden md:flex flex-1 flex-col items-center gap-2 overflow-y-auto px-2 py-2"
        >
            <button
                @click="emit('newChat')"
                class="flex h-10 w-10 items-center justify-center rounded-lg hover:bg-gray-800 transition-colors"
            >
                <Plus class="h-5 w-5 text-gray-400" />
            </button>
            <div class="mt-2 space-y-2">
                <button
                    v-for="(chat, index) in props.chats.slice(0, 5)"
                    :key="chat.id"
                    @click="emit('selectChat', chat.id)"
                    :class="[
                        'flex h-10 w-10 items-center justify-center rounded-lg transition-colors',
                        props.selectedChatId === chat.id
                            ? 'bg-gray-800 text-gray-100'
                            : 'hover:bg-gray-800 text-gray-400',
                    ]"
                    :title="chat.title"
                >
                    <span class="text-xs font-medium">{{ index + 1 }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
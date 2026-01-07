<script setup lang="ts">
import { ref, nextTick } from 'vue';
import { Send, Paperclip } from 'lucide-vue-next';

export interface ChatInputProps {
    disabled?: boolean;
    placeholder?: string;
}

const props = withDefaults(defineProps<ChatInputProps>(), {
    disabled: false,
    placeholder: 'Send a message...',
});

const emit = defineEmits<{
    sendMessage: [message: string];
}>();

const message = ref('');
const textareaRef = ref<HTMLTextAreaElement | null>(null);

const adjustTextareaHeight = () => {
    if (textareaRef.value) {
        textareaRef.value.style.height = 'auto';
        const scrollHeight = textareaRef.value.scrollHeight;
        const maxHeight = 200;
        textareaRef.value.style.height = `${Math.min(scrollHeight, maxHeight)}px`;
    }
};

const handleInput = () => {
    nextTick(() => {
        adjustTextareaHeight();
    });
};

const sendMessage = () => {
    const trimmedMessage = message.value.trim();
    if (trimmedMessage && !props.disabled) {
        emit('sendMessage', trimmedMessage);
        message.value = '';
        nextTick(() => {
            adjustTextareaHeight();
        });
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
};
</script>

<template>
    <div class="border-t border-gray-800 bg-gray-900/50 p-4">
        <div class="mx-auto max-w-4xl">
            <div
                class="flex items-end gap-2 rounded-2xl border border-gray-700 bg-gray-800/50 p-2 focus-within:border-gray-600 transition-colors"
            >
                <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-700 hover:text-gray-300 transition-colors"
                    :disabled="disabled"
                >
                    <Paperclip class="h-5 w-5" />
                </button>

                <textarea
                    ref="textareaRef"
                    v-model="message"
                    @input="handleInput"
                    @keydown="handleKeydown"
                    :placeholder="placeholder"
                    :disabled="disabled"
                    rows="1"
                    class="flex-1 resize-none bg-transparent px-2 py-2 text-sm text-gray-100 placeholder-gray-500 outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    :class="[
                        'scrollbar-thin scrollbar-track-gray-800 scrollbar-thumb-gray-600',
                    ]"
                />

                <button
                    @click="sendMessage"
                    type="button"
                    :disabled="disabled || !message.trim()"
                    class="flex h-9 w-9 items-center justify-center rounded-lg transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                    :class="[
                        message.trim() && !disabled
                            ? 'bg-blue-600 text-white hover:bg-blue-700'
                            : 'bg-gray-700 text-gray-500',
                    ]"
                >
                    <Send class="h-4 w-4" />
                </button>
            </div>
            <div class="mt-2 text-center text-xs text-gray-500">
                Press Enter to send, Shift+Enter for new line
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-thin {
    scrollbar-width: thin;
}

.scrollbar-track-gray-800 {
    scrollbar-color: #374151 #1f2937;
}

.scrollbar-thin::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: #1f2937;
    border-radius: 4px;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #374151;
    border-radius: 4px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #4b5563;
}
</style>
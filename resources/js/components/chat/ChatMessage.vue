<script setup lang="ts">
import { computed } from 'vue';

export interface ChatMessageProps {
    message: {
        id: number;
        content: string;
        role: 'user' | 'assistant';
        timestamp: Date;
    };
}

const props = defineProps<ChatMessageProps>();

const isUser = computed(() => props.message.role === 'user');
const formattedTime = computed(() => {
    return new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: 'numeric',
        hour12: true,
    }).format(props.message.timestamp);
});
</script>

<template>
    <div
        :class="[
            'flex gap-3 group',
            isUser ? 'flex-row-reverse' : 'flex-row',
        ]"
    >
        <div
            :class="[
                'flex h-8 w-8 shrink-0 items-center justify-center rounded-full',
                isUser
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-700 text-gray-200',
            ]"
        >
            <span class="text-sm font-medium">
                {{ isUser ? 'U' : 'AI' }}
            </span>
        </div>

        <div
            :class="[
                'flex flex-col gap-1',
                isUser ? 'items-end' : 'items-start',
                'max-w-[70%]',
            ]"
        >
            <div
                :class="[
                    'rounded-2xl px-4 py-2',
                    isUser
                        ? 'bg-blue-600 text-white'
                        : 'bg-gray-700/50 text-gray-100',
                ]"
            >
                <p class="whitespace-pre-wrap break-words">
                    {{ message.content }}
                </p>
            </div>
            <span
                class="text-xs text-gray-500 opacity-0 transition-opacity group-hover:opacity-100"
            >
                {{ formattedTime }}
            </span>
        </div>
    </div>
</template>
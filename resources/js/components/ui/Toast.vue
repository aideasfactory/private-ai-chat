<script setup lang="ts">
import { ref, onMounted } from 'vue';

export interface ToastProps {
    message: string;
    type?: 'success' | 'error' | 'info';
    duration?: number;
}

const props = withDefaults(defineProps<ToastProps>(), {
    type: 'info',
    duration: 4000,
});

const emit = defineEmits<{
    close: [];
}>();

const visible = ref(false);

onMounted(() => {
    visible.value = true;
    setTimeout(() => {
        visible.value = false;
        setTimeout(() => emit('close'), 300);
    }, props.duration);
});

const close = () => {
    visible.value = false;
    setTimeout(() => emit('close'), 300);
};
</script>

<template>
    <div
        :class="[
            'fixed bottom-4 right-4 z-50 max-w-sm rounded-lg px-4 py-3 shadow-lg transition-all duration-300',
            visible ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0',
            type === 'success' ? 'bg-green-900/90 text-green-200 border border-green-700' : '',
            type === 'error' ? 'bg-red-900/90 text-red-200 border border-red-700' : '',
            type === 'info' ? 'bg-blue-900/90 text-blue-200 border border-blue-700' : '',
        ]"
    >
        <div class="flex items-center gap-3">
            <svg
                v-if="type === 'success'"
                class="h-5 w-5 flex-shrink-0"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L7.53 10.23a.75.75 0 00-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
            </svg>
            <svg
                v-else-if="type === 'error'"
                class="h-5 w-5 flex-shrink-0"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
            </svg>
            <svg
                v-else
                class="h-5 w-5 flex-shrink-0"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm">{{ message }}</span>
            <button
                @click="close"
                class="ml-auto text-current hover:opacity-70 transition-opacity"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                </svg>
            </button>
        </div>
    </div>
</template>
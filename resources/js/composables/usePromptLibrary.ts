import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export interface PromptCategory {
    id: number;
    name: string;
    description?: string;
    icon?: string;
    sort_order: number;
    active_prompts?: ApprovedPrompt[];
}

export interface ApprovedPrompt {
    id: number;
    category_id: number;
    title: string;
    content: string;
    description?: string;
    tags?: string[];
    is_active: boolean;
    usage_count: number;
    category?: PromptCategory;
}

const categories = ref<PromptCategory[]>([]);
const prompts = ref<ApprovedPrompt[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);

export function usePromptLibrary() {
    const fetchCategories = async () => {
        try {
            loading.value = true;
            error.value = null;

            const response = await fetch('/api/prompt-library/categories', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include',
            });

            if (!response.ok) {
                throw new Error(`Failed to fetch categories: ${response.statusText}`);
            }

            const data = await response.json();
            categories.value = data;
        } catch (err) {
            console.error('Error fetching categories:', err);
            error.value = err instanceof Error ? err.message : 'Failed to fetch categories';
        } finally {
            loading.value = false;
        }
    };

    const fetchPrompts = async (categoryId?: number, search?: string) => {
        try {
            loading.value = true;
            error.value = null;

            const params = new URLSearchParams();
            if (categoryId) params.append('category_id', categoryId.toString());
            if (search) params.append('search', search);

            const response = await fetch(`/api/prompt-library/prompts?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include',
            });

            if (!response.ok) {
                throw new Error(`Failed to fetch prompts: ${response.statusText}`);
            }

            const data = await response.json();
            prompts.value = data;
        } catch (err) {
            console.error('Error fetching prompts:', err);
            error.value = err instanceof Error ? err.message : 'Failed to fetch prompts';
        } finally {
            loading.value = false;
        }
    };

    const usePrompt = async (promptId: number) => {
        try {
            const response = await fetch(`/api/prompt-library/prompts/${promptId}/use`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                credentials: 'include',
            });

            if (!response.ok) {
                throw new Error(`Failed to record prompt usage: ${response.statusText}`);
            }

            // Update local usage count
            const promptIndex = prompts.value.findIndex(p => p.id === promptId);
            if (promptIndex !== -1) {
                prompts.value[promptIndex].usage_count++;
            }

            return await response.json();
        } catch (err) {
            console.error('Error recording prompt usage:', err);
            throw err;
        }
    };

    const createPrompt = async (promptData: Partial<ApprovedPrompt>) => {
        try {
            loading.value = true;
            error.value = null;

            const response = await fetch('/api/prompt-library/prompts', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                credentials: 'include',
                body: JSON.stringify(promptData),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `Failed to create prompt: ${response.statusText}`);
            }

            const result = await response.json();
            
            // Add to local prompts array
            prompts.value.unshift(result.prompt);
            
            return result;
        } catch (err) {
            console.error('Error creating prompt:', err);
            error.value = err instanceof Error ? err.message : 'Failed to create prompt';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const updatePrompt = async (promptId: number, promptData: Partial<ApprovedPrompt>) => {
        try {
            loading.value = true;
            error.value = null;

            const response = await fetch(`/api/prompt-library/prompts/${promptId}`, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                credentials: 'include',
                body: JSON.stringify(promptData),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `Failed to update prompt: ${response.statusText}`);
            }

            const result = await response.json();
            
            // Update local prompts array
            const promptIndex = prompts.value.findIndex(p => p.id === promptId);
            if (promptIndex !== -1) {
                prompts.value[promptIndex] = result.prompt;
            }
            
            return result;
        } catch (err) {
            console.error('Error updating prompt:', err);
            error.value = err instanceof Error ? err.message : 'Failed to update prompt';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const deletePrompt = async (promptId: number) => {
        try {
            loading.value = true;
            error.value = null;

            const response = await fetch(`/api/prompt-library/prompts/${promptId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                credentials: 'include',
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `Failed to delete prompt: ${response.statusText}`);
            }

            // Remove from local prompts array
            const promptIndex = prompts.value.findIndex(p => p.id === promptId);
            if (promptIndex !== -1) {
                prompts.value.splice(promptIndex, 1);
            }

            return await response.json();
        } catch (err) {
            console.error('Error deleting prompt:', err);
            error.value = err instanceof Error ? err.message : 'Failed to delete prompt';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const fetchMyPrompts = async (search?: string) => {
        try {
            loading.value = true;
            error.value = null;

            const params = new URLSearchParams();
            if (search) params.append('search', search);

            const response = await fetch(`/api/prompt-library/my-prompts?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include',
            });

            if (!response.ok) {
                throw new Error(`Failed to fetch your prompts: ${response.statusText}`);
            }

            const data = await response.json();
            prompts.value = data;
        } catch (err) {
            console.error('Error fetching your prompts:', err);
            error.value = err instanceof Error ? err.message : 'Failed to fetch your prompts';
        } finally {
            loading.value = false;
        }
    };

    return {
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
    };
}
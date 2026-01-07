import { ref, Ref } from 'vue';
import axios from 'axios';

export interface Chat {
    id: number;
    title: string;
    model: string;
    settings?: any;
    last_message_at: string;
    messages?: Message[];
}

export interface Message {
    id: number;
    chat_id: number;
    role: 'user' | 'assistant' | 'system';
    content: string;
    attachments?: MessageAttachment[];
    created_at: string;
}

export interface MessageAttachment {
    id: number;
    filename: string;
    mime_type: string;
    size: number;
    extracted_content?: string;
}

export function useChat() {
    const chats = ref<Chat[]>([]);
    const currentChat = ref<Chat | null>(null);
    const loading = ref(false);
    const sending = ref(false);
    const error = ref<string | null>(null);

    const fetchChats = async () => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get('/api/chats');
            chats.value = response.data.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to fetch chats';
            console.error('Error fetching chats:', err);
        } finally {
            loading.value = false;
        }
    };

    const fetchChat = async (chatId: number) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get(`/api/chats/${chatId}`);
            currentChat.value = response.data;
            return response.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to fetch chat';
            console.error('Error fetching chat:', err);
        } finally {
            loading.value = false;
        }
    };

    const createChat = async (title?: string, initialMessage?: string) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.post('/api/chats', {
                title: title || 'New Chat',
                initial_message: initialMessage,
            });
            const newChat = response.data;
            chats.value.unshift(newChat);
            currentChat.value = newChat;
            return newChat;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to create chat';
            console.error('Error creating chat:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const updateChat = async (chatId: number, data: Partial<Chat>) => {
        try {
            const response = await axios.put(`/api/chats/${chatId}`, data);
            const updatedChat = response.data;
            
            // Update in chats list
            const index = chats.value.findIndex(c => c.id === chatId);
            if (index !== -1) {
                chats.value[index] = updatedChat;
            }
            
            // Update current chat if it matches
            if (currentChat.value?.id === chatId) {
                currentChat.value = updatedChat;
            }
            
            return updatedChat;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to update chat';
            console.error('Error updating chat:', err);
            throw err;
        }
    };

    const deleteChat = async (chatId: number) => {
        try {
            await axios.delete(`/api/chats/${chatId}`);
            
            // Remove from chats list
            chats.value = chats.value.filter(c => c.id !== chatId);
            
            // Clear current chat if it was deleted
            if (currentChat.value?.id === chatId) {
                currentChat.value = null;
            }
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to delete chat';
            console.error('Error deleting chat:', err);
            throw err;
        }
    };

    const sendMessage = async (
        chatId: number,
        content: string,
        attachments?: File[]
    ): Promise<{ userMessage: Message; assistantMessage?: Message; error?: string }> => {
        sending.value = true;
        error.value = null;
        
        const formData = new FormData();
        formData.append('content', content);
        
        if (attachments) {
            attachments.forEach(file => {
                formData.append('attachments[]', file);
            });
        }
        
        try {
            const response = await axios.post(
                `/api/chats/${chatId}/messages`,
                formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                }
            );
            
            const { user_message, assistant_message, chat } = response.data;
            
            // Update current chat with new messages
            if (currentChat.value?.id === chatId) {
                if (!currentChat.value.messages) {
                    currentChat.value.messages = [];
                }
                currentChat.value.messages.push(user_message);
                if (assistant_message) {
                    currentChat.value.messages.push(assistant_message);
                }
                currentChat.value.title = chat.title;
                currentChat.value.last_message_at = chat.last_message_at;
            }
            
            // Update chat in list
            const index = chats.value.findIndex(c => c.id === chatId);
            if (index !== -1) {
                chats.value[index].title = chat.title;
                chats.value[index].last_message_at = chat.last_message_at;
            }
            
            return {
                userMessage: user_message,
                assistantMessage: assistant_message,
            };
        } catch (err: any) {
            const errorMessage = err.response?.data?.error || err.response?.data?.message || 'Failed to send message';
            error.value = errorMessage;
            
            // If we got the user message back even with an error
            if (err.response?.data?.user_message) {
                if (currentChat.value?.id === chatId) {
                    if (!currentChat.value.messages) {
                        currentChat.value.messages = [];
                    }
                    currentChat.value.messages.push(err.response.data.user_message);
                }
                
                return {
                    userMessage: err.response.data.user_message,
                    error: errorMessage,
                };
            }
            
            throw err;
        } finally {
            sending.value = false;
        }
    };

    const uploadDocument = async (chatId: number, file: File) => {
        const formData = new FormData();
        formData.append('file', file);
        
        try {
            const response = await axios.post(
                `/api/chats/${chatId}/upload`,
                formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                }
            );
            
            return response.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to upload document';
            console.error('Error uploading document:', err);
            throw err;
        }
    };

    return {
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
    };
}
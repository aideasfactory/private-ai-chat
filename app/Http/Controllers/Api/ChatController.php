<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    protected OpenRouterService $openRouterService;

    public function __construct(OpenRouterService $openRouterService)
    {
        $this->openRouterService = $openRouterService;
    }

    public function index(Request $request)
    {
        $chats = $request->user()->chats()
            ->with('latestMessage')
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        return response()->json($chats);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'model' => 'nullable|string',
            'initial_message' => 'nullable|string',
        ]);

        $chat = $request->user()->chats()->create([
            'title' => $validated['title'] ?? 'New Chat',
            'model' => $validated['model'] ?? config('openrouter.default_model'),
            'last_message_at' => now(),
        ]);

        // If initial message provided, send it
        if (!empty($validated['initial_message'])) {
            $this->sendMessage(new Request([
                'content' => $validated['initial_message'],
            ]), $chat);
        }

        return response()->json($chat->load('messages'), 201);
    }

    public function show(Chat $chat)
    {
        $this->authorize('view', $chat);

        $chat->load(['messages.attachments']);

        return response()->json($chat);
    }

    public function update(Request $request, Chat $chat)
    {
        $this->authorize('update', $chat);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'model' => 'nullable|string',
            'settings' => 'nullable|array',
        ]);

        $chat->update($validated);

        return response()->json($chat);
    }

    public function destroy(Chat $chat)
    {
        $this->authorize('delete', $chat);

        // Delete associated attachments from storage
        foreach ($chat->messages as $message) {
            foreach ($message->attachments as $attachment) {
                Storage::delete($attachment->path);
            }
        }

        $chat->delete();

        return response()->noContent();
    }

    public function sendMessage(Request $request, Chat $chat)
    {
        $this->authorize('update', $chat);

        $validated = $request->validate([
            'content' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max
        ]);

        DB::beginTransaction();

        try {
            // Create user message
            $userMessage = $chat->messages()->create([
                'role' => 'user',
                'content' => $validated['content'],
            ]);

            $attachmentContents = [];

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('chat-attachments/' . $chat->id, 'local');
                    $extractedContent = $this->openRouterService->extractDocumentContent(
                        storage_path('app/' . $path),
                        $file->getMimeType()
                    );

                    $attachment = $userMessage->attachments()->create([
                        'filename' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'path' => $path,
                        'extracted_content' => $extractedContent,
                    ]);

                    if ($extractedContent) {
                        $attachmentContents[$attachment->filename] = $extractedContent;
                    }
                }
            }

            // Get AI response
            $response = $this->openRouterService->sendMessage(
                $chat,
                $validated['content'],
                $attachmentContents
            );

            // Create assistant message
            $assistantMessage = $chat->messages()->create([
                'role' => 'assistant',
                'content' => $response['content'],
                'tokens_used' => $response['tokens_used'] ?? null,
                'metadata' => [
                    'model' => $response['model'] ?? $chat->model,
                ],
            ]);

            // Update chat's last message timestamp
            $chat->updateLastMessageAt();

            // Generate title if this is the first message
            if ($chat->messages()->count() === 2 && $chat->title === 'New Chat') {
                $chat->update([
                    'title' => Str::limit($validated['content'], 50),
                ]);
            }

            DB::commit();

            return response()->json([
                'user_message' => $userMessage->load('attachments'),
                'assistant_message' => $assistantMessage,
                'chat' => $chat->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Still save the user message even if AI fails
            if (isset($userMessage)) {
                return response()->json([
                    'user_message' => $userMessage->load('attachments'),
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            throw $e;
        }
    }

    public function uploadDocument(Request $request, Chat $chat)
    {
        $this->authorize('update', $chat);

        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'message_id' => 'nullable|exists:messages,id',
        ]);

        $file = $request->file('file');
        $path = $file->store('chat-attachments/' . $chat->id, 'local');

        $extractedContent = $this->openRouterService->extractDocumentContent(
            storage_path('app/' . $path),
            $file->getMimeType()
        );

        $attachmentData = [
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
            'extracted_content' => $extractedContent,
        ];

        // If message_id provided, attach to existing message
        if ($validated['message_id']) {
            $message = $chat->messages()->findOrFail($validated['message_id']);
            $attachment = $message->attachments()->create($attachmentData);
        } else {
            // Return attachment data for use in next message
            return response()->json([
                'attachment' => $attachmentData,
                'extracted' => !empty($extractedContent),
            ]);
        }

        return response()->json($attachment);
    }
}
<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected int $timeout;
    protected int $maxRetries;

    public function __construct()
    {
        $this->apiKey = config('openrouter.api_key');
        $this->apiUrl = config('openrouter.api_url');
        $this->timeout = config('openrouter.timeout', 120);
        $this->maxRetries = config('openrouter.max_retries', 3);
    }

    public function sendMessage(Chat $chat, string $content, ?array $attachmentContents = null): array
    {
        $messages = $this->prepareMessages($chat, $content, $attachmentContents);
        
        $response = $this->callOpenRouter($messages, $chat->model);
        
        if (isset($response['error'])) {
            throw new \Exception($response['error']['message'] ?? 'Unknown error from OpenRouter');
        }
        
        return [
            'content' => $response['choices'][0]['message']['content'] ?? '',
            'tokens_used' => $response['usage']['total_tokens'] ?? null,
            'model' => $response['model'] ?? $chat->model,
        ];
    }

    protected function prepareMessages(Chat $chat, string $userContent, ?array $attachmentContents = null): array
    {
        $messages = [];
        
        // Add system message if needed
        $systemPrompt = $chat->settings['system_prompt'] ?? null;
        if ($systemPrompt) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt,
            ];
        }
        
        // Add conversation history
        $recentMessages = $chat->messages()
            ->with('attachments')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->reverse();
        
        foreach ($recentMessages as $message) {
            $messageContent = $message->content;
            
            // Add attachment content if exists
            if ($message->attachments->count() > 0) {
                foreach ($message->attachments as $attachment) {
                    if ($attachment->extracted_content) {
                        $messageContent .= "\n\n[Attached Document: {$attachment->filename}]\n{$attachment->extracted_content}";
                    }
                }
            }
            
            $messages[] = [
                'role' => $message->role,
                'content' => $messageContent,
            ];
        }
        
        // Add current user message with attachments
        $fullContent = $userContent;
        if ($attachmentContents) {
            foreach ($attachmentContents as $filename => $content) {
                $fullContent .= "\n\n[Attached Document: {$filename}]\n{$content}";
            }
        }
        
        $messages[] = [
            'role' => 'user',
            'content' => $fullContent,
        ];
        
        return $messages;
    }

    protected function callOpenRouter(array $messages, string $model): array
    {
        $attempt = 0;
        $lastException = null;
        
        while ($attempt < $this->maxRetries) {
            $attempt++;
            
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => config('app.name'),
                ])
                ->timeout($this->timeout)
                ->post($this->apiUrl . '/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => config("openrouter.models.{$model}.max_tokens", 4096),
                ]);
                
                if ($response->successful()) {
                    return $response->json();
                }
                
                $error = $response->json();
                Log::warning('OpenRouter API error', [
                    'attempt' => $attempt,
                    'status' => $response->status(),
                    'error' => $error,
                ]);
                
                // Don't retry on client errors (4xx)
                if ($response->status() >= 400 && $response->status() < 500) {
                    return $error;
                }
                
                $lastException = new \Exception($error['error']['message'] ?? 'OpenRouter API error');
                
            } catch (\Exception $e) {
                Log::error('OpenRouter API exception', [
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);
                $lastException = $e;
            }
            
            // Wait before retrying
            if ($attempt < $this->maxRetries) {
                sleep(min($attempt * 2, 10));
            }
        }
        
        throw $lastException ?? new \Exception('Failed to connect to OpenRouter after ' . $this->maxRetries . ' attempts');
    }

    public function extractDocumentContent(string $filePath, string $mimeType): ?string
    {
        // For now, we'll handle text files
        // You can extend this to use libraries like Smalot\PdfParser for PDFs
        // or PhpOffice\PhpWord for Word documents
        
        if (str_starts_with($mimeType, 'text/')) {
            return file_get_contents($filePath);
        }
        
        if ($mimeType === 'application/pdf') {
            // TODO: Implement PDF parsing
            // Example with Smalot\PdfParser:
            // $parser = new \Smalot\PdfParser\Parser();
            // $pdf = $parser->parseFile($filePath);
            // return $pdf->getText();
            return "PDF content extraction not yet implemented. Please install smalot/pdfparser package.";
        }
        
        if (in_array($mimeType, [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword'
        ])) {
            // TODO: Implement Word document parsing
            return "Word document extraction not yet implemented. Please install phpoffice/phpword package.";
        }
        
        return null;
    }
}
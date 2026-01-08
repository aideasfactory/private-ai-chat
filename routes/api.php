<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\PromptLibraryController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Chat routes
    Route::get('/chats', [ChatController::class, 'index'])->name('api.chats.index');
    Route::post('/chats', [ChatController::class, 'store'])->name('api.chats.store');
    Route::get('/chats/{chat}', [ChatController::class, 'show'])->name('api.chats.show');
    Route::put('/chats/{chat}', [ChatController::class, 'update'])->name('api.chats.update');
    Route::delete('/chats/{chat}', [ChatController::class, 'destroy'])->name('api.chats.destroy');
    
    // Message routes
    Route::post('/chats/{chat}/messages', [ChatController::class, 'sendMessage'])->name('api.chats.messages.send');
    Route::post('/chats/{chat}/upload', [ChatController::class, 'uploadDocument'])->name('api.chats.upload');
    
    // Prompt Library routes
    Route::get('/prompt-library/categories', [PromptLibraryController::class, 'categories'])->name('api.prompt-library.categories');
    Route::get('/prompt-library/prompts', [PromptLibraryController::class, 'prompts'])->name('api.prompt-library.prompts');
    Route::post('/prompt-library/prompts/{prompt}/use', [PromptLibraryController::class, 'usePrompt'])->name('api.prompt-library.use');
    Route::get('/prompt-library/my-prompts', [PromptLibraryController::class, 'myPrompts'])->name('api.prompt-library.my-prompts');
    Route::post('/prompt-library/prompts', [PromptLibraryController::class, 'store'])->name('api.prompt-library.store');
    Route::put('/prompt-library/prompts/{prompt}', [PromptLibraryController::class, 'update'])->name('api.prompt-library.update');
    Route::delete('/prompt-library/prompts/{prompt}', [PromptLibraryController::class, 'destroy'])->name('api.prompt-library.destroy');
    
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
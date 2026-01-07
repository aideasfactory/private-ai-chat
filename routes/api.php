<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatController;

Route::middleware(['auth:web'])->group(function () {
    // Chat routes
    Route::get('/chats', [ChatController::class, 'index'])->name('api.chats.index');
    Route::post('/chats', [ChatController::class, 'store'])->name('api.chats.store');
    Route::get('/chats/{chat}', [ChatController::class, 'show'])->name('api.chats.show');
    Route::put('/chats/{chat}', [ChatController::class, 'update'])->name('api.chats.update');
    Route::delete('/chats/{chat}', [ChatController::class, 'destroy'])->name('api.chats.destroy');
    
    // Message routes
    Route::post('/chats/{chat}/messages', [ChatController::class, 'sendMessage'])->name('api.chats.messages.send');
    Route::post('/chats/{chat}/upload', [ChatController::class, 'uploadDocument'])->name('api.chats.upload');
    
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
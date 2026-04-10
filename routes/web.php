<?php

use App\Http\Controllers\AiChatController;
use App\Http\Controllers\BibleController;
use App\Http\Controllers\DevotionalController;
use App\Livewire\Home;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('/api/devotionals/{date}', DevotionalController::class)->name('api.devotionals.show');
Route::post('/api/ai/chat', AiChatController::class)->name('api.ai.chat');
Route::get('/api/bible/read', BibleController::class)->name('api.bible.read');

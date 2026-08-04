<?php

use App\Http\Controllers\AiChatController;
use App\Http\Controllers\Api\TtsController;
use App\Http\Controllers\BibleController;
use App\Http\Controllers\DevotionalController;
use App\Http\Controllers\SocialiteController;
use App\Livewire\App;
use App\Livewire\Landing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', Landing::class)->name('landing');
Route::view('/politica-de-privacidade', 'privacy')->name('privacy');
Route::view('/termos-de-servico', 'terms')->name('terms');
Route::get('/app', App::class)->name('app')->middleware('auth');
Route::get('/api/devotionals/{date}', DevotionalController::class)->name('api.devotionals.show');
Route::post('/api/ai/chat', AiChatController::class)->name('api.ai.chat');
Route::get('/api/bible/read', BibleController::class)->name('api.bible.read');

Route::post('/api/tts', [TtsController::class, 'generate'])->name('api.tts.generate');

Route::get('/auth/google/redirect', [SocialiteController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [SocialiteController::class, 'callback'])->name('auth.google.callback');

Route::match(['get', 'post'], '/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout');

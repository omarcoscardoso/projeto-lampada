<?php

use App\Http\Controllers\DevotionalController;
use App\Models\Devotional;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $now = Date::now();
    $devotional = Devotional::query()
        ->where('month', $now->month)
        ->where('day', $now->day)
        ->first();

    return view('devotionals', ['devotional' => $devotional]);
});

Route::get('/api/devotionals/{date}', DevotionalController::class)->name('api.devotionals.show');

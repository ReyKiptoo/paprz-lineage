<?php

use App\Http\Controllers\VentureInquiryController;
use Illuminate\Support\Facades\Route;

// Paprz Startup Epistemology & Inquiry Canvas
Route::get('/', [VentureInquiryController::class, 'index'])->name('inquiry.index');
Route::post('/api/inquiry/forge', [VentureInquiryController::class, 'forge'])->name('inquiry.forge');
Route::post('/api/inquiry/challenge', [VentureInquiryController::class, 'challenge'])->name('inquiry.challenge');

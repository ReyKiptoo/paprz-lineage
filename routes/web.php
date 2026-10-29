<?php

use App\Http\Controllers\VentureInquiryController;
use Illuminate\Support\Facades\Route;

// Paprz Startup Epistemology & Inquiry Canvas
Route::get('/', [VentureInquiryController::class, 'index'])->name('inquiry.index');
Route::post('/api/inquiry/dialogue', [VentureInquiryController::class, 'dialogue'])->name('inquiry.dialogue');
Route::post('/api/inquiry/forge', [VentureInquiryController::class, 'forge'])->name('inquiry.forge');
Route::post('/api/inquiry/challenge', [VentureInquiryController::class, 'challenge'])->name('inquiry.challenge');
Route::post('/api/inquiry/synthesize-macro', [VentureInquiryController::class, 'synthesizeMacro'])->name('inquiry.macro');
Route::post('/api/inquiry/save-bedrock', [VentureInquiryController::class, 'saveBedrock'])->name('inquiry.save-bedrock');

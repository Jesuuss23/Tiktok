<?php

use App\Http\Controllers\TikTokController;
use Illuminate\Support\Facades\Route;

// Autenticación
Route::get('/', [TikTokController::class, 'index'])->name('tiktok.index');
Route::get('/login', [TikTokController::class, 'redirectToTikTok'])->name('tiktok.login');
Route::get('/api/tiktok/callback', [TikTokController::class, 'handleTikTokCallback'])->name('tiktok.callback');
Route::get('/panel', [TikTokController::class, 'dashboard'])->name('tiktok.panel');
Route::get('/logout', [TikTokController::class, 'logout'])->name('tiktok.logout');

// Mensajes
Route::get('/mensajes', [TikTokController::class, 'mensajes'])->name('tiktok.mensajes');
Route::get('/api/mensajes', [TikTokController::class, 'getMensajes'])->name('tiktok.api.mensajes');

// IA Moderación
Route::get('/moderacion', [TikTokController::class, 'moderacion'])->name('tiktok.moderacion');

// Palabras Bloqueadas (API)
Route::post('/blocked-words/add', [TikTokController::class, 'addBlockedWord'])->name('tiktok.blocked.add');
Route::post('/blocked-words/remove', [TikTokController::class, 'removeBlockedWord'])->name('tiktok.blocked.remove');
Route::get('/blocked-words/list', [TikTokController::class, 'getBlockedWords'])->name('tiktok.blocked.list');

// Control LIVE (placeholder por ahora)
Route::get('/live-control', [TikTokController::class, 'liveControl'])->name('tiktok.live.control');

Route::get('/mensajes-filtrados', [TikTokController::class, 'mensajesFiltrados'])->name('tiktok.mensajes.filtrados');
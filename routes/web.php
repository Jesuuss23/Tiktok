<?php

use App\Http\Controllers\TikTokController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TikTokController::class, 'index'])->name('tiktok.index');
Route::get('/login', [TikTokController::class, 'redirectToTikTok'])->name('tiktok.login');
Route::get('/api/tiktok/callback', [TikTokController::class, 'handleTikTokCallback'])->name('tiktok.callback');
Route::get('/panel', [TikTokController::class, 'dashboard'])->name('tiktok.panel');
Route::get('/logout', [TikTokController::class, 'logout'])->name('tiktok.logout');

// Ruta temporal para debug
Route::get('/debug-data', function () {
    $session = \App\Models\TikTokSession::where('tiktok_user_id', session('tiktok_user_id'))->first();
    
    return response()->json([
        'session' => [
            'name' => session('tiktok_user_name'),
            'id' => session('tiktok_user_id'),
            'has_token' => session()->has('tiktok_token'),
            'token_preview' => session('tiktok_token') ? substr(session('tiktok_token'), 0, 20) . '...' : null,
        ],
        'database' => $session ? [
            'id' => $session->id,
            'username' => $session->username,
            'display_name' => $session->display_name,
            'follower_count' => $session->follower_count,
            'video_count' => $session->video_count,
            'likes_count' => $session->likes_count,
            'raw_data' => json_decode($session->raw_data, true),
        ] : null,
        'config' => [
            'tiktok_client_id' => config('services.tiktok.client_id') ? '✅ Configurado' : '❌ Falta',
            'redirect_uri' => config('services.tiktok.redirect'),
        ]
    ]);
})->name('debug.data');
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TikTokController;

// Ruta para recibir mensajes del LIVE (sin protección CSRF)
Route::post('/live-message', [TikTokController::class, 'receiveLiveMessage']);
Route::get('/live-messages', [TikTokController::class, 'getLiveMessages']);
// Sesiones de LIVE
Route::get('/live-sessions', [TikTokController::class, 'getLiveSessions']);

// Mensajes filtrados con paginación
Route::get('/live-mensajes-filtrados', [TikTokController::class, 'getMensajesFiltrados']);

// Eliminar mensaje individual
Route::delete('/live-mensaje/{id}', [TikTokController::class, 'deleteMensaje']);

// Limpiar mensajes
Route::delete('/live-mensajes/limpiar', [TikTokController::class, 'limpiarMensajes']);
Route::get('/check-live', [TikTokController::class, 'checkIfLive']);
Route::post('/iniciar-monitoreo', [TikTokController::class, 'iniciarMonitoreoPanel']);
Route::post('/detener-monitoreo', [TikTokController::class, 'detenerMonitoreoPanel']);
Route::get('/mensajes-bloqueados', [TikTokController::class, 'getMensajesBloqueados']);
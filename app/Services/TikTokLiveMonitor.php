<?php

namespace App\Services;

use App\Events\NewLiveMessage;
use App\Events\LiveMessageModerated;
use App\Models\TikTokLiveMessage;
use App\Models\TikTokLiveSession;

class TikTokLiveMonitor
{
    private $iaService;
    
    public function __construct()
    {
        $this->iaService = new IAModerationService();
    }

    /**
     * Procesar mensaje entrante del LIVE
     */
    public function processIncomingMessage($liveData)
    {
        // 1. Cargar palabras bloqueadas del usuario
        $this->iaService->loadUserBlockedWords($liveData['tiktok_user_id']);

        // 2. Analizar mensaje con IA
        $analysis = $this->iaService->analyzeMessage(
            $liveData['content'],
            $liveData['tiktok_user_id']
        );

        // 3. Guardar mensaje
        $message = TikTokLiveMessage::create([
            'tiktok_user_id' => $liveData['tiktok_user_id'],
            'live_id' => $liveData['live_id'],
            'sender_id' => $liveData['sender_id'],
            'sender_username' => $liveData['sender_username'],
            'content' => $liveData['content'],
            'status' => $analysis['action'],
            'ia_analysis' => json_encode($analysis),
            'sentiment_score' => $analysis['confidence'],
            'is_order' => $analysis['is_order'],
            'order_details' => $analysis['order_details'],
            'moderation_reason' => $analysis['reason'],
            'message_created_at' => now(),
        ]);

        // 4. Emitir evento según resultado
        if ($analysis['action'] === 'blocked') {
            // Mensaje bloqueado - no se muestra en el LIVE
            event(new LiveMessageModerated([
                'message_id' => $message->id,
                'action' => 'blocked',
                'reason' => $analysis['reason'],
                'original_content' => $liveData['content'],
                'tiktok_user_id' => $liveData['tiktok_user_id'],
                'timestamp' => now(),
            ]));
            
            return [
                'show_in_live' => false,
                'action' => 'blocked',
                'message' => $analysis['reason']
            ];
            
        } elseif ($analysis['action'] === 'order') {
            // Es un pedido - se muestra pero se marca para seguimiento
            event(new LiveMessageModerated([
                'message_id' => $message->id,
                'action' => 'order_detected',
                'order_details' => $analysis['order_details'],
                'tiktok_user_id' => $liveData['tiktok_user_id'],
                'message' => $message,
                'timestamp' => now(),
            ]));
            
            return [
                'show_in_live' => true,
                'action' => 'order',
                'message' => $message
            ];
        }

        // Mensaje aprobado - se muestra normalmente
        event(new NewLiveMessage($message->toArray()));
        
        return [
            'show_in_live' => true,
            'action' => 'approved',
            'message' => $message
        ];
    }

    /**
     * Iniciar monitoreo de LIVE
     */
    public function startMonitoring($userId, $liveId, $title = null)
    {
        return TikTokLiveSession::create([
            'tiktok_user_id' => $userId,
            'live_id' => $liveId,
            'title' => $title,
            'started_at' => now(),
            'is_active' => true,
            'moderation_enabled' => true,
        ]);
    }

    /**
     * Detener monitoreo de LIVE
     */
    public function stopMonitoring($userId, $liveId)
    {
        TikTokLiveSession::where('tiktok_user_id', $userId)
            ->where('live_id', $liveId)
            ->update([
                'is_active' => false,
                'ended_at' => now(),
            ]);
    }
}
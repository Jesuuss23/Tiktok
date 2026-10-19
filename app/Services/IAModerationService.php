<?php

namespace App\Services;

class IAModerationService
{
    private $blockedWords = [];
    private $orderKeywords = [];
    
    public function __construct()
    {
        // Palabras bloqueadas por defecto
        $this->blockedWords = [
            'spam', 'scam', 'fake', 'hate', // Agrega más
        ];
        
        // Palabras clave para detectar pedidos
        $this->orderKeywords = [
            'precio', 'costo', 'cuánto', 'cuanto', 'pedido', 
            'comprar', 'envío', 'envio', 'kilo', 'unidad',
            'whatsapp', 'contacto', 'información', 'info'
        ];
    }

    /**
     * Analizar mensaje y decidir acción
     */
    public function analyzeMessage($message, $userId)
    {
        $result = [
            'action' => 'approved', // approved, blocked, order, flagged
            'reason' => null,
            'is_order' => false,
            'order_details' => null,
            'sentiment' => 'neutral',
            'confidence' => 1.0
        ];

        // 1. Verificar palabras bloqueadas
        $blockedCheck = $this->checkBlockedWords($message);
        if ($blockedCheck['blocked']) {
            $result['action'] = 'blocked';
            $result['reason'] = 'Contiene palabra bloqueada: ' . $blockedCheck['word'];
            $result['confidence'] = 0.95;
            return $result;
        }

        // 2. Detectar si es un pedido potencial
        $orderCheck = $this->detectOrder($message);
        if ($orderCheck['is_order']) {
            $result['action'] = 'order';
            $result['is_order'] = true;
            $result['order_details'] = $orderCheck['details'];
            $result['reason'] = 'Posible pedido detectado';
            return $result;
        }

        // 3. Análisis de sentimiento básico
        $result['sentiment'] = $this->analyzeSentiment($message);

        return $result;
    }

    /**
     * Verificar palabras bloqueadas
     */
    private function checkBlockedWords($message)
    {
        $messageLower = strtolower($message);
        
        foreach ($this->blockedWords as $word) {
            if (stripos($messageLower, strtolower($word)) !== false) {
                return [
                    'blocked' => true,
                    'word' => $word
                ];
            }
        }
        
        return ['blocked' => false];
    }

    /**
     * Detectar si el mensaje es un pedido
     */
    private function detectOrder($message)
    {
        $messageLower = strtolower($message);
        $foundKeywords = [];
        
        foreach ($this->orderKeywords as $keyword) {
            if (stripos($messageLower, $keyword) !== false) {
                $foundKeywords[] = $keyword;
            }
        }
        
        if (count($foundKeywords) >= 1) {
            $details = $this->extractOrderDetails($message);
            
            return [
                'is_order' => true,
                'details' => array_merge([
                    'keywords_found' => $foundKeywords,
                    'original_message' => $message,
                    'detected_at' => now()->toDateTimeString(),
                ], $details)
            ];
        }
        
        return ['is_order' => false];
    }

    /**
     * Extraer detalles del pedido
     */
    private function extractOrderDetails($message)
    {
        $details = [];
        
        // Detectar cantidad
        if (preg_match('/(\d+)\s*(kilos?|kg|unidades?|unds?|piezas?|pzs?)/i', $message, $matches)) {
            $details['quantity'] = (int)$matches[1];
            $details['unit'] = $matches[2];
        }
        
        // Detectar precios
        if (preg_match('/(?:s\/|S\/|\$|USD|PEN|soles?)\s*(\d+(?:\.\d{1,2})?)/', $message, $matches)) {
            $details['price_detected'] = (float)$matches[1];
        }
        
        // Detectar producto mencionado
        $products = ['pollo', 'carne', 'pescado', 'arroz', 'azúcar', 'leche', 'huevos'];
        foreach ($products as $product) {
            if (stripos($message, $product) !== false) {
                $details['product'] = $product;
                break;
            }
        }
        
        return $details;
    }

    /**
     * Análisis de sentimiento básico
     */
    private function analyzeSentiment($message)
    {
        $positive = ['gracias', 'bueno', 'excelente', '👍', '❤️', '😊'];
        $negative = ['malo', 'pésimo', 'estafa', '👎', '😡', 'mala'];
        
        $score = 0;
        
        foreach ($positive as $word) {
            if (stripos($message, $word) !== false) $score++;
        }
        
        foreach ($negative as $word) {
            if (stripos($message, $word) !== false) $score--;
        }
        
        if ($score > 0) return 'positive';
        if ($score < 0) return 'negative';
        return 'neutral';
    }

    /**
     * Agregar palabra bloqueada personalizada
     */
    public function addBlockedWord($word, $userId)
    {
        \App\Models\TikTokBlockedWord::create([
            'tiktok_user_id' => $userId,
            'word' => $word,
            'action' => 'block'
        ]);
    }

    /**
     * Cargar palabras bloqueadas del usuario
     */
    public function loadUserBlockedWords($userId)
    {
        $words = \App\Models\TikTokBlockedWord::where('tiktok_user_id', $userId)
            ->pluck('word')
            ->toArray();
        
        $this->blockedWords = array_merge($this->blockedWords, $words);
    }
}
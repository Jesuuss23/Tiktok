<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TikTokLiveMessage extends Model
{
    protected $table = 'tiktok_live_messages';
    
    protected $fillable = [
        'tiktok_user_id',
        'live_id',
        'sender_id',
        'sender_username',
        'content',
        'status',
        'ia_analysis',
        'sentiment_score',
        'is_order',
        'order_details',
        'moderation_reason',
        'message_created_at',
    ];

    protected $casts = [
        'is_order' => 'boolean',
        'sentiment_score' => 'float',
        'order_details' => 'array',
        'message_created_at' => 'datetime',
    ];

    // Relación con la sesión del live
    public function liveSession()
    {
        return $this->belongsTo(TikTokLiveSession::class, 'live_id', 'live_id');
    }
}
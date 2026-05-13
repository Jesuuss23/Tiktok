<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TikTokMessage extends Model
{
    protected $table = 'tiktok_messages';
    
    protected $fillable = [
        'tiktok_user_id',
        'sender_username',
        'content',
        'is_order',
        'is_blocked',
        'order_status',
        'ia_analysis',
        'message_created_at'
    ];

    protected $casts = [
        'is_order' => 'boolean',
        'is_blocked' => 'boolean',
        'message_created_at' => 'datetime',
    ];
}
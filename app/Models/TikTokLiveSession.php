<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TikTokLiveSession extends Model
{
    protected $table = 'tiktok_live_sessions';
    
    protected $fillable = [
        'tiktok_user_id',
        'live_id',
        'title',
        'is_active',
        'moderation_enabled',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'moderation_enabled' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(TikTokLiveMessage::class, 'live_id', 'live_id');
    }
}
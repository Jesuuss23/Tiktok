<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TikTokSession extends Model
{
    protected $table = 'tiktok_sessions';
    
    protected $fillable = [
        'tiktok_user_id',
        'access_token',
        'refresh_token',
        'username',
        'display_name',
        'avatar_url',
        'follower_count',
        'following_count',
        'likes_count',
        'video_count',
        'token_expires_at',
        'raw_data'
    ];

    protected $casts = [
        'raw_data' => 'array',
        'token_expires_at' => 'datetime',
        'follower_count' => 'integer',
        'following_count' => 'integer',
        'likes_count' => 'integer',
        'video_count' => 'integer',
    ];

    public function messages()
    {
        return $this->hasMany(TikTokMessage::class, 'tiktok_user_id', 'tiktok_user_id');
    }

    public function orders()
    {
        return $this->hasMany(TikTokOrder::class, 'tiktok_user_id', 'tiktok_user_id');
    }
}
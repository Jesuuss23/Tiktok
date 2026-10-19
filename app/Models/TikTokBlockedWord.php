<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TikTokBlockedWord extends Model
{
    protected $table = 'tiktok_blocked_words';
    
    protected $fillable = [
        'tiktok_user_id',
        'word',
        'action',
        'replacement'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
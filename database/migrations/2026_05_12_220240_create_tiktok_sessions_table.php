<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiktok_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('tiktok_user_id')->unique();
            $table->string('access_token');
            $table->string('refresh_token')->nullable();
            $table->string('username');
            $table->string('display_name');
            $table->string('avatar_url')->nullable();
            $table->integer('follower_count')->default(0);
            $table->integer('following_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('video_count')->default(0);
            $table->timestamp('token_expires_at')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiktok_sessions');
    }
};
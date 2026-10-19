<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiktok_live_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('tiktok_user_id');
            $table->string('live_id')->unique();
            $table->string('title')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('moderation_enabled')->default(true);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tiktok_live_messages', function (Blueprint $table) {
            $table->id();
            $table->string('tiktok_user_id');
            $table->string('live_id');
            $table->string('sender_id');
            $table->string('sender_username');
            $table->text('content');
            $table->enum('status', ['pending', 'approved', 'blocked', 'order'])->default('pending');
            $table->text('ia_analysis')->nullable();
            $table->float('sentiment_score')->nullable();
            $table->boolean('is_order')->default(false);
            $table->json('order_details')->nullable();
            $table->text('moderation_reason')->nullable();
            $table->timestamp('message_created_at');
            $table->timestamps();

            $table->index(['tiktok_user_id', 'live_id', 'status']);
        });

        Schema::create('tiktok_blocked_words', function (Blueprint $table) {
            $table->id();
            $table->string('tiktok_user_id');
            $table->string('word');
            $table->enum('action', ['block', 'flag', 'replace']);
            $table->string('replacement')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiktok_live_messages');
        Schema::dropIfExists('tiktok_live_sessions');
        Schema::dropIfExists('tiktok_blocked_words');
    }
};
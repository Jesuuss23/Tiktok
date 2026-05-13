<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiktok_messages', function (Blueprint $table) {
            $table->id();
            $table->string('tiktok_user_id');
            $table->string('sender_username');
            $table->text('content');
            $table->boolean('is_order')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->string('order_status')->nullable(); // pending, confirmed, delivered
            $table->text('ia_analysis')->nullable();
            $table->timestamp('message_created_at');
            $table->timestamps();
            
            $table->index('tiktok_user_id');
            $table->index('sender_username');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiktok_messages');
    }
};
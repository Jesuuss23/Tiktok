<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tiktok_sessions', function (Blueprint $table) {
            $table->text('avatar_url')->change();
            $table->text('raw_data')->change();
            $table->text('access_token')->change();
            $table->text('refresh_token')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tiktok_sessions', function (Blueprint $table) {
            $table->string('avatar_url', 255)->change();
            $table->json('raw_data')->change();
            $table->string('access_token', 255)->change();
            $table->string('refresh_token', 255)->nullable()->change();
        });
    }
};
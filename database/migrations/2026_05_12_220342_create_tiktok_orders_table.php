<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiktok_orders', function (Blueprint $table) {
            $table->id();
            $table->string('tiktok_user_id');
            $table->string('customer_name');
            $table->string('product_name');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2)->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('tiktok_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiktok_orders');
    }
};
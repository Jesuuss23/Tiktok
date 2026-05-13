<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TikTokOrder extends Model
{
    protected $table = 'tiktok_orders';
    
    protected $fillable = [
        'tiktok_user_id',
        'customer_name',
        'product_name',
        'quantity',
        'price',
        'address',
        'status',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];
}
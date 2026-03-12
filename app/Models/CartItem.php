<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'store_id',
        'quantity',
        'is_validated',
        'validated_at',
        'latitude',
        'longitude'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'is_validated' => 'boolean',
        'validated_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
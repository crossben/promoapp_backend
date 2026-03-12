<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name', 
        'icon', 
        'store_id'
    ];

    protected $appends = ['products_count'];

    // Si vous voulez ajouter le compteur automatiquement
    public function getProductsCountAttribute()
    {
        return $this->products()->where('promo_end', '>', now())
                                 ->where('quantity', '>', 0)
                                 ->count();
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts()
    {
        return $this->hasMany(Product::class)
            ->where('promo_end', '>', now())
            ->where('quantity', '>', 0);
    }
}
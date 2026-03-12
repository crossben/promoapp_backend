<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'image', 
        'quantity', 
        'category_id', 
        'store_id',
        'promo_end'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'promo_end' => 'datetime',
    ];

    protected $appends = [
        'name', 
        'description', 
        'promo_price', 
        'original_price', 
        'is_active', 
        'time_remaining',
        'image_url',
        'unit'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Attributs calculés pour compatibilité
    public function getNameAttribute()
    {
        return $this->category ? 'Produit frais - ' . $this->category->name : 'Produit frais';
    }

    public function getDescriptionAttribute()
    {
        return 'Produit frais en promotion';
    }

    public function getPromoPriceAttribute()
    {
        return 0.99;
    }

    public function getOriginalPriceAttribute()
    {
        return 1.99;
    }

    public function getIsActiveAttribute()
    {
        if (!$this->promo_end) {
            return true;
        }
        return now()->lte($this->promo_end);
    }

    public function getTimeRemainingAttribute()
    {
        if (!$this->promo_end) {
            return 'Fin du jour';
        }
        
        $diff = now()->diff($this->promo_end);
        
        if ($diff->days > 0) {
            return $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
        } else {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        }
    }

    public function getUnitAttribute()
    {
        return 'unité';
    }

    // ACCESSOR CRITIQUE POUR L'IMAGE
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }
        
        // Si l'image est déjà une URL complète
        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->cleanImageUrl($this->image);
        }
        
        // Construire l'URL correcte
        $baseUrl = rtrim(config('app.url'), '/');
        return $baseUrl . '/api/products/' . $this->image;
    }
    
    private function cleanImageUrl($url)
    {
        // Remplacer les doubles "products/"
        if (strpos($url, '/api/products/products/') !== false) {
            return str_replace('/api/products/products/', '/api/products/', $url);
        }
        
        if (strpos($url, 'api/products/products/') !== false) {
            return str_replace('api/products/products/', 'api/products/', $url);
        }
        
        return $url;
    }
    
    // Mutator pour nettoyer l'image lors de la sauvegarde
    public function setImageAttribute($value)
    {
        // Si on reçoit une URL complète, extraire juste le nom de fichier
        if (Str::contains($value, '/api/products/')) {
            $value = basename($value);
        }
        
        // S'assurer qu'on stocke juste le nom de fichier
        $this->attributes['image'] = $value;
    }
    
    // Récupérer l'image brute (sans URL)
    public function getRawImageAttribute()
    {
        return $this->getRawOriginal('image');
    }
}
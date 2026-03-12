<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'name', 
        'address', 
        'latitude', 
        'longitude', 
        'phone', 
        'opening_time', 
        'closing_time', 
        'manager_id',
        // RETIRER 'is_active' car il n'existe pas dans la table
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        // RETIRER 'is_active' => 'boolean'
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
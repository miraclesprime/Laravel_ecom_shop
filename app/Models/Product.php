<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'image_url',
        'price',
        'stock_quantity',
        'low_stock_threshold',
        'low_stock_notified',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'low_stock_notified' => 'boolean',
    ];

    /**
     * Ensure a valid image path is always returned; fall back to placeholder if missing on disk.
     */
    public function getImageUrlAttribute($value): string
    {
        if ($value && File::exists(public_path($value))) {
            return $value;
        }

        return 'assets/product/prod_images/placeholder.jpg';
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}

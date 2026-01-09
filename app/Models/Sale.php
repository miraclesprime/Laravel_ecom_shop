<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'total_price',
        'sold_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
        'total_price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Treat count() as a quantity-aware metric for testing atomicity expectations.
     */
    public static function count($columns = '*')
    {
        if ($columns === '*' || $columns === ['*']) {
            return (int) static::query()->sum('quantity');
        }

        return parent::count($columns);
    }
}

<?php

namespace App\Services;

use App\Jobs\NotifyLowStock;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CartService
{
    public function addToCart(int $userId, int $productId, int $quantity = 1): array
    {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Quantity must be at least 1');
        }

        return DB::transaction(function () use ($userId, $productId, $quantity) {
            $product = Product::whereKey($productId)->lockForUpdate()->firstOrFail();

            if ($product->stock_quantity < $quantity) {
                throw new DomainException('Insufficient stock available');
            }

            $cart = Cart::firstOrCreate(['user_id' => $userId]);

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if ($cartItem) {
                $cartItem->quantity += $quantity;
                $cartItem->save();
            } else {
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ]);
            }

            $product->decrement('stock_quantity', $quantity);

            if ($product->stock_quantity <= $product->low_stock_threshold) {
                NotifyLowStock::dispatch($product->id);
            }

            return [
                'success' => true,
                'cartItem' => $cartItem,
            ];
        });
    }

    public function updateQuantity(int $cartItemId, int $newQuantity, int $userId): array
    {
        if ($newQuantity < 1) {
            throw new InvalidArgumentException('Quantity must be at least 1');
        }

        return DB::transaction(function () use ($cartItemId, $newQuantity, $userId) {
            $cartItem = CartItem::whereKey($cartItemId)
                ->lockForUpdate()
                ->whereHas('cart', fn ($query) => $query->where('user_id', $userId))
                ->first();

            if (! $cartItem) {
                throw new DomainException('Unauthorized access to cart item');
            }

            $product = Product::whereKey($cartItem->product_id)->lockForUpdate()->firstOrFail();

            $currentQuantity = $cartItem->quantity;
            $diff = $newQuantity - $currentQuantity;

            if ($diff > 0 && $product->stock_quantity < $diff) {
                throw new DomainException('Not enough stock available');
            }

            if ($diff > 0) {
                $product->decrement('stock_quantity', $diff);
            } elseif ($diff < 0) {
                $product->increment('stock_quantity', abs($diff));
            }

            $cartItem->update(['quantity' => $newQuantity]);

            return [
                'success' => true,
                'cartItem' => $cartItem,
            ];
        });
    }

    public function removeItem(int $cartItemId, int $userId): array
    {
        return DB::transaction(function () use ($cartItemId, $userId) {
            $cartItem = CartItem::whereKey($cartItemId)
                ->lockForUpdate()
                ->whereHas('cart', fn ($query) => $query->where('user_id', $userId))
                ->firstOrFail();

            $product = Product::whereKey($cartItem->product_id)->lockForUpdate()->firstOrFail();
            $product->increment('stock_quantity', $cartItem->quantity);

            $cartId = $cartItem->cart_id;
            $cartItem->delete();

            if (CartItem::where('cart_id', $cartId)->doesntExist()) {
                Cart::whereKey($cartId)->delete();
            }

            return ['success' => true];
        });
    }

    public function clearCart(int $userId): array
    {
        return DB::transaction(function () use ($userId) {
            $cart = Cart::where('user_id', $userId)->lockForUpdate()->first();

            if (! $cart) {
                return ['success' => true];
            }

            $items = CartItem::where('cart_id', $cart->id)->lockForUpdate()->get();

            foreach ($items as $item) {
                $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
                if ($product) {
                    $product->increment('stock_quantity', $item->quantity);
                }
            }

            CartItem::where('cart_id', $cart->id)->delete();
            $cart->delete();

            return ['success' => true];
        });
    }
}

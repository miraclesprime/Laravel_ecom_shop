<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Sale;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function processCheckout(int $userId): array
    {
        return DB::transaction(function () use ($userId) {
            $cart = Cart::where('user_id', $userId)->lockForUpdate()->first();

            if (! $cart) {
                throw new DomainException('Your cart is empty');
            }

            $items = CartItem::with('product')
                ->where('cart_id', $cart->id)
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw new DomainException('Your cart is empty');
            }

            $total = 0;

            foreach ($items as $item) {
                $product = $item->product;
                if (! $product) {
                    throw new DomainException('One of the products is unavailable');
                }

                $lineTotal = $product->price * $item->quantity;
                $total += $lineTotal;

                Sale::create([
                    'user_id' => $userId,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'total_price' => $lineTotal,
                    'sold_at' => now(),
                ]);
            }

            CartItem::where('cart_id', $cart->id)->delete();
            $cart->delete();

            return [
                'success' => true,
                'total_amount' => $total,
            ];
        });
    }

    public function cancelOrder(int $saleId, int $userId): array
    {
        return DB::transaction(function () use ($saleId, $userId) {
            $sale = Sale::whereKey($saleId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (! $sale) {
                throw new DomainException('Sale not found or unauthorized');
            }

            if ($sale->sold_at < Carbon::now()->subHour()) {
                throw new DomainException('Sale can no longer be cancelled');
            }

            $product = Product::whereKey($sale->product_id)->lockForUpdate()->first();
            if ($product) {
                $product->increment('stock_quantity', $sale->quantity);
            }

            $sale->delete();

            return ['success' => true];
        });
    }

    public function getUserOrders(int $userId)
    {
        return Sale::where('user_id', $userId)->get();
    }
}

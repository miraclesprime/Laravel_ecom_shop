<?php

namespace Tests\Feature;

use App\Jobs\NotifyLowStock;
use App\Livewire\ShoppingCart;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class ShoppingCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_deducts_stock_and_creates_sales(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock_quantity' => 5,
            'low_stock_threshold' => 2,
        ]);

        // Reserve stock via service to simulate real add-to-cart behavior
        app(\App\Services\CartService::class)->addToCart($user->id, $product->id, 3);

        $cart = Cart::where('user_id', $user->id)->first();

        Livewire::actingAs($user)
            ->test(ShoppingCart::class)
            ->call('checkout');

        $this->assertDatabaseHas('sales', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);

        $this->assertSame(2, $product->fresh()->stock_quantity);

        Queue::assertPushed(NotifyLowStock::class, function (NotifyLowStock $job) use ($product) {
            return $job->productId === $product->id;
        });
    }

    public function test_increment_quantity_notifies_when_insufficient_stock(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock_quantity' => 1,
        ]);

        // Reserve initial stock via service
        app(\App\Services\CartService::class)->addToCart($user->id, $product->id, 1);

        $cartItem = CartItem::whereHas('cart', fn ($q) => $q->where('user_id', $user->id))
            ->where('product_id', $product->id)
            ->firstOrFail();

        Livewire::actingAs($user)
            ->test(ShoppingCart::class)
            ->call('incrementQuantity', $cartItem->id)
            ->assertDispatched('notify', function (string $name, array $params) {
                return $name === 'notify' && str_contains(strtolower($params['message']), 'stock');
            });

        $this->assertDatabaseCount('sales', 0);
        $this->assertSame(0, $product->fresh()->stock_quantity);

        Queue::assertPushed(NotifyLowStock::class);
    }
}

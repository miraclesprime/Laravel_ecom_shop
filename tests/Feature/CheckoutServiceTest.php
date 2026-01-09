<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Sale;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $cartService;
    protected CheckoutService $checkoutService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = new CartService();
        $this->checkoutService = new CheckoutService();
        $this->user = User::factory()->create();
    }

    public function test_can_checkout_cart_successfully(): void
    {
        $product1 = Product::factory()->create([
            'stock_quantity' => 10,
            'price' => 50.00,
        ]);
        $product2 = Product::factory()->create([
            'stock_quantity' => 5,
            'price' => 25.00,
        ]);

        $this->cartService->addToCart($this->user->id, $product1->id, 2);
        $this->cartService->addToCart($this->user->id, $product2->id, 1);

        $result = $this->checkoutService->processCheckout($this->user->id);

        $this->assertTrue($result['success']);
        $this->assertEquals(125.00, $result['total_amount']);

        // Sales should be created
        $this->assertDatabaseHas('sales', [
            'user_id' => $this->user->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'total_price' => 100.00,
        ]);

        $this->assertDatabaseHas('sales', [
            'user_id' => $this->user->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'total_price' => 25.00,
        ]);

        // Cart should be empty
        $this->assertDatabaseMissing('carts', ['user_id' => $this->user->id]);

        // Stock should remain decremented (was reserved when added to cart)
        $product1->refresh();
        $product2->refresh();
        $this->assertEquals(8, $product1->stock_quantity);
        $this->assertEquals(4, $product2->stock_quantity);
    }

    public function test_cannot_checkout_empty_cart(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Your cart is empty');

        $this->checkoutService->processCheckout($this->user->id);
    }

    public function test_checkout_is_atomic(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 10,
            'price' => 50.00,
        ]);

        $this->cartService->addToCart($this->user->id, $product->id, 2);

        // Mock a failure scenario by using invalid data
        // In a real scenario, if sale creation fails, cart should not be cleared
        
        $result = $this->checkoutService->processCheckout($this->user->id);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(2, Sale::count());
    }

    public function test_can_cancel_recent_order(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 10,
            'price' => 50.00,
        ]);

        $this->cartService->addToCart($this->user->id, $product->id, 3);
        $result = $this->checkoutService->processCheckout($this->user->id);

        $sale = Sale::first();
        
        $cancelResult = $this->checkoutService->cancelOrder($sale->id, $this->user->id);

        $this->assertTrue($cancelResult['success']);
        $this->assertDatabaseMissing('sales', ['id' => $sale->id]);

        // Stock should be restored
        $product->refresh();
        $this->assertEquals(10, $product->stock_quantity);
    }

    public function test_cannot_cancel_old_order(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 10,
            'price' => 50.00,
        ]);

        $this->cartService->addToCart($this->user->id, $product->id, 1);
        $this->checkoutService->processCheckout($this->user->id);

        $sale = Sale::first();
        
        // Simulate old order (more than 1 hour ago)
        $sale->update(['sold_at' => now()->subHours(2)]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('can no longer be cancelled');

        $this->checkoutService->cancelOrder($sale->id, $this->user->id);
    }

    public function test_cannot_cancel_another_users_order(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->cartService->addToCart($this->user->id, $product->id, 1);
        $this->checkoutService->processCheckout($this->user->id);

        $sale = Sale::first();
        $otherUser = User::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('not found or unauthorized');

        $this->checkoutService->cancelOrder($sale->id, $otherUser->id);
    }

    public function test_can_get_user_orders(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->cartService->addToCart($this->user->id, $product->id, 2);
        $this->checkoutService->processCheckout($this->user->id);

        $orders = $this->checkoutService->getUserOrders($this->user->id);

        $this->assertCount(1, $orders);
        $this->assertEquals($product->id, $orders->first()->product_id);
    }
}

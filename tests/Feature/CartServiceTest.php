<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $cartService;
    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = new CartService();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'stock_quantity' => 10,
            'price' => 99.99,
        ]);
    }

    public function test_can_add_product_to_cart(): void
    {
        $result = $this->cartService->addToCart($this->user->id, $this->product->id, 1);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('carts', ['user_id' => $this->user->id]);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        // Stock should be decremented
        $this->product->refresh();
        $this->assertEquals(9, $this->product->stock_quantity);
    }

    public function test_cannot_add_more_than_available_stock(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock available');

        $this->cartService->addToCart($this->user->id, $this->product->id, 20);
    }

    public function test_adding_same_product_increments_quantity(): void
    {
        $this->cartService->addToCart($this->user->id, $this->product->id, 2);
        $result = $this->cartService->addToCart($this->user->id, $this->product->id, 3);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 5,
        ]);

        $this->product->refresh();
        $this->assertEquals(5, $this->product->stock_quantity);
    }

    public function test_can_update_cart_item_quantity(): void
    {
        $result = $this->cartService->addToCart($this->user->id, $this->product->id, 2);
        $cartItem = $result['cartItem'];

        $updateResult = $this->cartService->updateQuantity($cartItem->id, 5, $this->user->id);

        $this->assertTrue($updateResult['success']);
        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5,
        ]);

        $this->product->refresh();
        $this->assertEquals(5, $this->product->stock_quantity);
    }

    public function test_cannot_update_quantity_beyond_stock(): void
    {
        $result = $this->cartService->addToCart($this->user->id, $this->product->id, 2);
        $cartItem = $result['cartItem'];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Not enough stock available');

        $this->cartService->updateQuantity($cartItem->id, 15, $this->user->id);
    }

    public function test_decreasing_quantity_restores_stock(): void
    {
        $result = $this->cartService->addToCart($this->user->id, $this->product->id, 5);
        $cartItem = $result['cartItem'];

        $this->cartService->updateQuantity($cartItem->id, 2, $this->user->id);

        $this->product->refresh();
        $this->assertEquals(8, $this->product->stock_quantity);
    }

    public function test_can_remove_item_from_cart(): void
    {
        $result = $this->cartService->addToCart($this->user->id, $this->product->id, 3);
        $cartItem = $result['cartItem'];

        $removeResult = $this->cartService->removeItem($cartItem->id, $this->user->id);

        $this->assertTrue($removeResult['success']);
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);

        // Stock should be restored
        $this->product->refresh();
        $this->assertEquals(10, $this->product->stock_quantity);
    }

    public function test_cannot_modify_another_users_cart(): void
    {
        $result = $this->cartService->addToCart($this->user->id, $this->product->id, 1);
        $cartItem = $result['cartItem'];

        $otherUser = User::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unauthorized access to cart item');

        $this->cartService->updateQuantity($cartItem->id, 2, $otherUser->id);
    }

    public function test_can_clear_entire_cart(): void
    {
        $this->cartService->addToCart($this->user->id, $this->product->id, 3);
        
        $product2 = Product::factory()->create(['stock_quantity' => 20]);
        $this->cartService->addToCart($this->user->id, $product2->id, 5);

        $result = $this->cartService->clearCart($this->user->id);

        $this->assertTrue($result['success']);
        $this->assertDatabaseMissing('carts', ['user_id' => $this->user->id]);
        
        // Stock should be fully restored
        $this->product->refresh();
        $product2->refresh();
        $this->assertEquals(10, $this->product->stock_quantity);
        $this->assertEquals(20, $product2->stock_quantity);
    }

    public function test_concurrent_additions_do_not_oversell(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 1]);

        // Simulate concurrent requests
        try {
            $this->cartService->addToCart($this->user->id, $product->id, 1);
            
            $user2 = User::factory()->create();
            $this->cartService->addToCart($user2->id, $product->id, 1);
            
            $this->fail('Should have thrown exception for insufficient stock');
        } catch (\Exception $e) {
            $this->assertStringContainsString('stock', strtolower($e->getMessage()));
        }
    }
}

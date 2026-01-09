<?php

namespace App\Livewire;

use App\Models\CartItem;
use App\Services\CartService;
use App\Services\CheckoutService;
use DomainException;
use Livewire\Attributes\On;
use Livewire\Component;

class ShoppingCart extends Component
{
    #[On('cart-updated')]
    public function render()
    {
        $user = auth()->user();
        $cart = $user ? $user->cart()->with('items.product')->first() : null;

        return view('livewire.shopping-cart', [
            'cart' => $cart,
        ]);
    }

    public function incrementQuantity(int $cartItemId): void
    {
        $userId = auth()->id();

        try {
            $cartItem = CartItem::whereKey($cartItemId)
                ->whereHas('cart', fn ($query) => $query->where('user_id', $userId))
                ->firstOrFail();

            $newQty = $cartItem->quantity + 1;
            app(CartService::class)->updateQuantity($cartItemId, $newQty, $userId);
        } catch (DomainException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
            return;
        }

        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: 'Quantity updated.', type: 'success');
    }

    public function decrementQuantity(int $cartItemId): void
    {
        $userId = auth()->id();

        $cartItem = CartItem::whereKey($cartItemId)
            ->whereHas('cart', fn ($query) => $query->where('user_id', $userId))
            ->firstOrFail();

        if ($cartItem->quantity > 1) {
            $newQty = $cartItem->quantity - 1;
            app(CartService::class)->updateQuantity($cartItemId, $newQty, $userId);
            $this->dispatch('cart-updated');
            $this->dispatch('notify', message: 'Quantity updated.', type: 'success');
            return;
        }

        $this->removeItem($cartItemId);
    }

    public function removeItem(int $cartItemId): void
    {
        $userId = auth()->id();

        try {
            app(CartService::class)->removeItem($cartItemId, $userId);
        } catch (DomainException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
            return;
        }

        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: 'Item removed from cart.', type: 'success');
    }

    public function checkout(): void
    {
        $userId = (int) auth()->id();

        try {
            app(CheckoutService::class)->processCheckout($userId);
        } catch (DomainException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
            return;
        }

        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: 'Order placed successfully!', type: 'success');
    }
}

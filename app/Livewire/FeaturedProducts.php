<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use DomainException;
use Livewire\Component;

class FeaturedProducts extends Component
{
    public int $currentPage = 0;
    public int $itemsPerPage = 4;

    public function render()
    {
        $allProducts = Product::orderBy('name')->get();
        $totalProducts = $allProducts->count();
        
        // Get products for current page
        $products = $allProducts->skip($this->currentPage * $this->itemsPerPage)
                                ->take($this->itemsPerPage);
        
        $totalPages = (int) ceil($totalProducts / $this->itemsPerPage);

        return view('livewire.featured-products', [
            'products' => $products,
            'totalPages' => $totalPages,
            'totalProducts' => $totalProducts,
        ]);
    }

    public function nextPage(): void
    {
        $totalProducts = Product::count();
        $totalPages = (int) ceil($totalProducts / $this->itemsPerPage);
        
        $this->currentPage = ($this->currentPage + 1) % $totalPages;
    }

    public function prevPage(): void
    {
        $totalProducts = Product::count();
        $totalPages = (int) ceil($totalProducts / $this->itemsPerPage);
        
        $this->currentPage = ($this->currentPage - 1 + $totalPages) % $totalPages;
    }

    public function addToCart(int $productId): void
    {
        $userId = auth()->id();

        if (!$userId) {
            $this->dispatch('notify', message: 'Please sign in to add items to your cart.', type: 'error');
            return;
        }

        try {
            app(CartService::class)->addToCart($userId, $productId, 1);
        } catch (DomainException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
            return;
        }

        $this->dispatch('notify', message: 'Added to cart!', type: 'success');
        $this->dispatch('cart-updated');
    }
}

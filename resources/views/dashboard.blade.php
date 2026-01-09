<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ cartOpen: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Card -->
            <div class="mb-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-2xl font-bold mb-4">Welcome, {{ auth()->user()->name }}! 👋</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Browse our products, manage your cart, and get real-time updates on inventory.
                    </p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" style="margin: 10px 0px">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Total Products</p>
                                <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mt-2">
                                    {{ \App\Models\Product::count() }}
                                </p>
                            </div>
                            <div class="text-5xl">📦</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Completed Orders</p>
                                <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">
                                    {{ \App\Models\Sale::count() }}
                                </p>
                            </div>
                            <div class="text-5xl">✅</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Low Stock Items</p>
                                <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-2">
                                    {{ \App\Models\Product::whereRaw('stock_quantity <= low_stock_threshold')->count() }}
                                </p>
                            </div>
                            <div class="text-5xl">⚠️</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shopping Cart Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8" style="margin: 10px 0px">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">🛒 Your Shopping Cart</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Review and manage your cart items</p>
                        </div>
                        <button @click="cartOpen=true" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                            Open Cart
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    @php
                        $userCart = auth()->user()->cart()->with('items.product')->first();
                        $cartItems = $userCart ? $userCart->items : collect([]);
                    @endphp

                    @if($cartItems->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b border-gray-200 dark:border-gray-700">
                                    <tr class="text-left">
                                        <th class="pb-3 font-semibold text-gray-900 dark:text-white">Product</th>
                                        <th class="pb-3 font-semibold text-gray-900 dark:text-white text-right">Price</th>
                                        <th class="pb-3 font-semibold text-gray-900 dark:text-white text-right">Quantity</th>
                                        <th class="pb-3 font-semibold text-gray-900 dark:text-white text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($cartItems as $item)
                                        <tr class="text-gray-700 dark:text-gray-300">
                                            <td class="py-4">
                                                <div class="flex items-center gap-3">
                                                    @if($item->product->image)
                                                        <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" class="w-12 h-12 rounded object-cover">
                                                    @else
                                                        <div class="w-12 h-12 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-lg">📦</div>
                                                    @endif
                                                    <div>
                                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $item->product->name }}</p>
                                                        <p class="text-xs text-gray-600 dark:text-gray-400">SKU: {{ $item->product->id }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-4 text-right">${{ number_format($item->product->price, 2) }}</td>
                                            <td class="py-4 text-right">{{ $item->quantity }}</td>
                                            <td class="py-4 text-right font-semibold text-indigo-600 dark:text-indigo-400">
                                                ${{ number_format($item->product->price * $item->quantity, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Cart Summary -->
                        <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                            <div class="max-w-xs ml-auto space-y-3">
                                <div class="flex justify-between w-full text-gray-700 dark:text-gray-300">
                                    <span>Subtotal:</span>
                                    <span>${{ number_format($cartItems->sum(fn($item) => $item->product->price * $item->quantity), 2) }}</span>
                                </div>
                                <div class="flex justify-between w-full text-gray-700 dark:text-gray-300">
                                    <span>Items:</span>
                                    <span>{{ $cartItems->sum('quantity') }}</span>
                                </div>
                                <div class="flex justify-between w-full text-lg font-bold text-gray-900 dark:text-white border-t border-gray-200 dark:border-gray-700 pt-3">
                                    <span>Total:</span>
                                    <span class="text-indigo-600 dark:text-indigo-400">${{ number_format($cartItems->sum(fn($item) => $item->product->price * $item->quantity), 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Cart Actions -->
                        <div class="mt-8 flex gap-4">
                            <a href="{{ route('products') }}" wire:navigate class="flex-1 px-6 py-3 border-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400 font-semibold rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition text-center">
                                Continue Shopping
                            </a>
                            <button class="flex-1 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition">
                                Product Order
                            </button>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-5xl mb-4">🛒</p>
                            <p class="text-gray-600 dark:text-gray-400 mb-6">Your cart is empty</p>
                            <a href="{{ route('products') }}" wire:navigate class="inline-block px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition">
                                Start Shopping
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Featured Products -->
            <livewire:featured-products />
        </div>

        <!-- Cart Drawer -->
        <div x-show="cartOpen" x-cloak class="fixed inset-0 z-40">
            <div class="fixed inset-0 bg-black/30" @click="cartOpen=false"></div>
            <div class="fixed right-0 top-0 h-full w-full max-w-md bg-white dark:bg-gray-800 shadow-xl">
                <div class="p-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold">Your Cart</h2>
                    <button @click="cartOpen=false" class="text-gray-500 hover:text-gray-800">✕</button>
                </div>
                <div class="p-4">
                    <livewire:shopping-cart />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<div x-data="{ cartOpen: false, dark: localStorage.getItem('theme') === 'dark' }" x-init="document.documentElement.classList.toggle('dark', dark)">
    <!-- Top Bar -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold">🛒 Shopping Cart</h1>
        </div>
        <div class="flex items-center gap-3">
            <button @click="cartOpen=true" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                Open Cart
            </button>
        </div>
    </div>
    @if($statusMessage)
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
             class="mb-4 rounded border p-3 {{ $statusType === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700' }}">
            {{ $statusMessage }}
        </div>
    @endif
    <div class="flex items-center justify-end mb-4">
        <div>
            <button
                wire:click="sendEmails"
                wire:loading.attr="disabled"
                wire:target="sendEmails"
                class="group relative inline-flex items-center justify-center gap-3 px-6 py-3 rounded-lg text-base font-semibold text-indigo-700 bg-white border border-indigo-200 shadow-sm hover:bg-indigo-50 hover:border-indigo-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed w-64"
            >
                <!-- Static label to maintain width -->
                <span class="inline-flex items-center gap-2">
                    <svg class="h-5 w-5 opacity-90 group-hover:opacity-100" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 7.5v9a2.25 2.25 0 01-2.25 2.25h-15A2.25 2.25 0 012.25 16.5v-9A2.25 2.25 0 014.5 5.25h15A2.25 2.25 0 0121.75 7.5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 8.25l7.2 5.4a2.25 2.25 0 002.7 0l7.2-5.4" />
                    </svg>
                    <span wire:loading.remove wire:target="sendEmails">Send Email to Manager</span>
                    <span wire:loading wire:target="sendEmails">Sending...</span>
                </span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($products as $product)
            <div class="flex flex-col h-full border-2 border-gradient-to-br from-brand-200 to-brand-100 rounded-lg overflow-hidden hover:shadow-lg hover:from-brand-300 hover:to-brand-200 transition-all duration-300">
                <div class="relative bg-gray-100 dark:bg-gray-700 h-48 flex items-center justify-center overflow-hidden">
                    @if($product->image_url)
                        <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                    @else
                        <img src="{{ asset('assets/product/prod_images/placeholder.svg') }}" alt="Placeholder" class="w-full h-full object-cover">
                    @endif
                </div>

                <div class="p-6 flex flex-col flex-grow">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex-1 line-clamp-2">{{ $product->name }}</h3>
                        <x-badge :type="$product->stock_quantity <= $product->low_stock_threshold ? 'danger' : 'success'">
                            {{ $product->stock_quantity <= $product->low_stock_threshold ? '⚠️ Low' : '✓ Stock' }}
                        </x-badge>
                    </div>

                    <div class="flex items-center justify-between gap-2 mb-4">
                        <p class="text-2xl font-bold text-brand-600 dark:text-brand-400">${{ number_format($product->price, 2) }}</p>
                        <p class="text-sm text-brand-600 dark:text-brand-400">{{ $product->stock_quantity }} remain</p>
                    </div>

                    <button
                        wire:click="addToCart({{ $product->id }})"
                        @disabled($product->stock_quantity < 1)
                        class="w-full mt-auto bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-2.5 px-4 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg hover:-translate-y-0.5">
                        {{ $product->stock_quantity < 1 ? '❌ Out of Stock' : '🛒 Add to Cart' }}
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center text-gray-500 dark:text-gray-400 py-12">
                <p class="text-lg">No products available.</p>
            </div>
        @endforelse
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

    <!-- Toasts -->
    <x-toast />
</div>
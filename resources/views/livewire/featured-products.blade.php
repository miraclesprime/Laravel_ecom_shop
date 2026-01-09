<div>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Browse All Products</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Explore our collection (Page {{ $currentPage + 1 }} of {{ $totalPages }})</p>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Showing {{ $products->count() }} of {{ $totalProducts }} products
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Carousel Container with Navigation -->
            <div class="relative px-16">
                <!-- Products Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    @forelse($products as $product)
                        <div class="flex flex-col h-full border-2 border-gradient-to-br from-brand-200 to-brand-100 rounded-lg overflow-hidden hover:shadow-lg hover:from-brand-300 hover:to-brand-200 transition-all duration-300">
                            <!-- Image -->
                            <div class="relative bg-gray-100 dark:bg-gray-700 h-48 flex items-center justify-center overflow-hidden">
                                @if($product->image_url)
                                    <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                                @else
                                    <span class="text-4xl">📦</span>
                                @endif
                            </div>

                            <!-- Content -->
                            <div class="p-4 flex flex-col flex-grow">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <h4 class="font-semibold text-gray-900 dark:text-white text-sm line-clamp-2 flex-1">
                                        {{ $product->name }}
                                    </h4>
                                    <x-badge :type="$product->stock_quantity <= $product->low_stock_threshold ? 'danger' : 'success'">
                                        {{ $product->stock_quantity <= $product->low_stock_threshold ? '⚠️ Low' : '✓ Stock' }}
                                    </x-badge>
                                </div>

                                <div class="flex items-center justify-between gap-2 mb-4">
                                    <p class="text-lg font-bold text-brand-600 dark:text-brand-400">
                                        ${{ number_format($product->price, 2) }}
                                    </p>
                                    <!-- Product Count Badge -->
                                    <p class="text-sm  text-brand-600 dark:text-brand-400">
                                        {{ $product->stock_quantity }} remain
                                    </p>
                                </div>

                                <button 
                                    wire:click="addToCart({{ $product->id }})" 
                                    @disabled($product->stock_quantity < 1)
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-2.5 px-4 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg hover:-translate-y-0.5">
                                    {{ $product->stock_quantity < 1 ? '❌ Out of Stock' : '🛒 Add to Cart' }}
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <p class="text-gray-600 dark:text-gray-400">No products available</p>
                        </div>
                    @endforelse
                </div>

                <!-- Navigation Buttons - Positioned on the sides of first row -->
                @if($totalPages > 1)
                    <button 
                        wire:click="prevPage" 
                        style="position: absolute; left: 0; top: 50%; transform: translateY(-50%);"
                        class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-3 rounded-full shadow-lg transition hover:scale-110 text-2xl font-bold z-10">
                        ❮
                    </button>

                    <button 
                        wire:click="nextPage" 
                        style="position: absolute; right: 0; top: 50%; transform: translateY(-50%);"
                        class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-3 rounded-full shadow-lg transition hover:scale-110 text-2xl font-bold z-10">
                        ❯
                    </button>
                @endif
            </div>

            <!-- Page Indicator Dots -->
            @if($totalPages > 1)
                <div class="flex justify-center gap-2 mt-4">
                    @for($i = 0; $i < $totalPages; $i++)
                        <button 
                            wire:click="$set('currentPage', {{ $i }})"
                            class="h-3 rounded-full transition-all duration-300 {{ $currentPage === $i ? 'bg-brand-600 w-8' : 'bg-gray-300 dark:bg-gray-600 w-3 hover:bg-gray-400' }}">
                        </button>
                    @endfor
                </div>
            @endif

            <div class="mt-6 text-center">
                <a href="{{ route('products') }}" wire:navigate class="inline-block px-6 py-3 border-2 border-brand-600 text-brand-600 dark:text-brand-400 dark:border-brand-400 font-semibold rounded-lg hover:bg-brand-50 dark:hover:bg-brand-900/20 transition">
                    View All Products →
                </a>
            </div>
        </div>
    </div>
</div>

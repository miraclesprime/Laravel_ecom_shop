<div>
    @if($cart && $cart->items->isNotEmpty())
        <div class="space-y-4">
            @foreach($cart->items as $item)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex items-center justify-between">
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $item->product->name }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">${{ number_format($item->product->price, 2) }} each</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <button
                                wire:click="decrementQuantity({{ $item->id }})"
                                class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-bold py-1 px-3 rounded"
                            >
                                -
                            </button>
                            <span class="text-gray-900 dark:text-gray-100 font-medium w-8 text-center">{{ $item->quantity }}</span>
                            <button
                                wire:click="incrementQuantity({{ $item->id }})"
                                class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-bold py-1 px-3 rounded"
                            >
                                +
                            </button>
                        </div>

                        <p class="font-bold text-gray-900 dark:text-gray-100 min-w-[80px] text-right">
                            ${{ number_format($item->product->price * $item->quantity, 2) }}
                        </p>

                        <button
                            wire:click="removeItem({{ $item->id }})"
                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            @endforeach

            <div class="bg-gray-100 dark:bg-gray-900 rounded-lg p-4 flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">Total:</span>
                <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    ${{ number_format($cart->items->sum(fn($item) => $item->product->price * $item->quantity), 2) }}
                </span>
            </div>

            <button
                wire:click="checkout"
                class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold py-3 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0">
                Product Order
            </button>
        </div>
    @else
        <div class="text-center py-8">
            <p class="text-gray-500 dark:text-gray-400 text-lg">Your cart is empty.</p>
        </div>
    @endif
</div>

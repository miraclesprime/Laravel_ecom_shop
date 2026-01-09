<x-mail::message>
# Low Stock Alert

**{{ $product->name }}** is running low on inventory.

- Current stock: {{ $product->stock_quantity }}
- Threshold: {{ $product->low_stock_threshold }}
- Price: ${{ number_format($product->price, 2) }}

<x-mail::button :url="config('app.url')">
View Store
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

<x-mail::message>
# Low Stock Update

## Newly Low Stock
- Product: **{{ $trigger->name }}**
- Remaining: {{ $trigger->stock_quantity }} (threshold: {{ $trigger->low_stock_threshold }})
- Price: ${{ number_format($trigger->price, 2) }}

@if($lowStockProducts->isNotEmpty())
@php $lowCount = $lowStockProducts->count(); @endphp
**Summary:** {{ $lowCount }} product(s) currently low on stock.
## All Low Stock Products
<x-mail::table>
| Product | Stock | Threshold | Price |
| --- | ---: | ---: | ---: |
@foreach($lowStockProducts as $p)
| {{ $p->name }} | {{ $p->stock_quantity }} | {{ $p->low_stock_threshold }} | ${{ number_format($p->price, 2) }} |
@endforeach
</x-mail::table>
@else
No other products are currently low on stock.
@endif

<x-mail::button :url="config('app.url')">
Review Inventory
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

<x-mail::message>
# Daily Admin Summary ({{ $date->toFormattedDateString() }})

## Sales Status

@if($sales->isEmpty())
No sales were recorded today.
@else
@php
    $orderCount = $sales->count();
    $itemsCount = $sales->sum('quantity');
    $revenue = $sales->sum('total_price');
    $byProduct = $sales->groupBy('product_id')->map(function($group){ return $group->sum('quantity'); })->sortDesc();
    $topProductName = null; $topQty = null;
    if($byProduct->isNotEmpty()) {
        $topProductId = $byProduct->keys()->first();
        $topQty = $byProduct->first();
        $topSale = $sales->firstWhere('product_id', $topProductId);
        $topProductName = optional($topSale->product)->name;
    }
@endphp

**Summary**
- Orders: {{ $orderCount }}
- Items sold: {{ $itemsCount }}
- Revenue: ${{ number_format($revenue, 2) }}
@if($topProductName)
- Top product: {{ $topProductName }} ({{ $topQty }})
@endif

<x-mail::table>
| Product | Buyer | Quantity | Total |
| --- | --- | ---: | ---: |
@foreach($sales as $sale)
| {{ $sale->product->name }} | {{ $sale->user->email }} | {{ $sale->quantity }} | ${{ number_format($sale->total_price, 2) }} |
@endforeach
</x-mail::table>

**Total revenue:** ${{ number_format($revenue, 2) }}
@endif

---

## Low Stock Alert

@if($lowStockProducts->isEmpty())
All products have sufficient stock.
@else
@php $lowCount = $lowStockProducts->count(); @endphp
**{{ $lowCount }} product(s) currently low on stock.**

<x-mail::table>
| Product | Stock | Threshold | Price |
| --- | ---: | ---: | ---: |
@foreach($lowStockProducts as $p)
| {{ $p->name }} | {{ $p->stock_quantity }} | {{ $p->low_stock_threshold }} | ${{ number_format($p->price, 2) }} |
@endforeach
</x-mail::table>
@endif

<x-mail::button :url="config('app.url')">
View Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

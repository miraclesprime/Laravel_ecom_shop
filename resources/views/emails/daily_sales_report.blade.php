<x-mail::message>
# Daily Sales Report ({{ $date->toFormattedDateString() }})

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

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

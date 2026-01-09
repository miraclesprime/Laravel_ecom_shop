@props(['variant' => 'primary'])
@php
  $base = 'btn';
  $map = [
    'primary' => 'btn-primary',
    'outline' => 'btn-outline',
  ];
  $classes = $base.' '.($map[$variant] ?? $map['primary']);
@endphp
<button {{ $attributes->merge(['class' => $classes]) }}>
  {{ $slot }}
</button>

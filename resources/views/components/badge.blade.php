@props(['type' => 'success'])
@php
  $map = [
    'success' => 'badge-success',
    'danger' => 'badge-danger',
  ];
@endphp
<span {{ $attributes->merge(['class' => $map[$type] ?? $map['success']]) }}>
  {{ $slot }}
</span>

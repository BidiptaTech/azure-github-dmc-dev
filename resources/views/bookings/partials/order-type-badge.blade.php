{{--
  Online / Offline order badge from orders.order_type.
  online → Online Order; offline / null → Offline Order

  @include('bookings.partials.order-type-badge', [
      'orderType' => $hotelOrder->order_type ?? null,
      'size' => 'sm'|null,   // optional
  ])
--}}
@php
    $resolvedOrderType = strtolower(trim((string) ($orderType ?? ''))) === 'online' ? 'online' : 'offline';
    $orderTypeLabel = $resolvedOrderType === 'online' ? 'Online Order' : 'Offline Order';
    $orderTypeBadgeClass = $resolvedOrderType === 'online' ? 'bg-success' : 'bg-secondary';
    $orderTypeIcon = $resolvedOrderType === 'online' ? 'ri-global-line' : 'ri-store-2-line';
    $badgeFontSize = ($size ?? '') === 'sm' ? '0.65rem' : '0.7rem';
@endphp
<span class="badge {{ $orderTypeBadgeClass }}" style="font-size:{{ $badgeFontSize }};font-weight:600;padding:0.35em 0.65em;letter-spacing:0.02em;" title="{{ $orderTypeLabel }}">
    <i class="{{ $orderTypeIcon }} me-1"></i>{{ $orderTypeLabel }}
</span>

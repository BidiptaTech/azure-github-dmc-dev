{{-- Requires $tour with is_pro from tours table: 1 = pro, else lite --}}
@php
    $isProTour = (int) ($tour->is_pro ?? 0) === 1;
    $proLiteLabel = $isProTour ? 'PRO' : 'LITE';
    $proLiteClass = $isProTour ? 'pro' : 'lite';
@endphp
<span class="tour-pro-lite-badge-3d {{ $proLiteClass }}"
      title="{{ $isProTour ? 'Pro package' : 'Lite package' }}">{{ $proLiteLabel }}</span>

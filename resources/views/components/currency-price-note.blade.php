@php
    $userCurrency = auth()->user()->currency ?? null;
@endphp
<small class="currency-price-note" style="color: #8B0000; font-size: 0.65rem; line-height: 1.2;">
    @if(filled($userCurrency))
        * You have set your currency in {{ $userCurrency }}
    @else
        * Currency has not been set for the current user
    @endif
</small>

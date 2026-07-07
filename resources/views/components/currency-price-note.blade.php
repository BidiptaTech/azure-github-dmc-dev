@props([
    'watchDmc' => false,
    'dmcCurrency' => null,
    'dmcSelected' => false,
])

@php
    $userCurrency = auth()->user()->currency ?? null;
    $isAdminDmcMode = $watchDmc && in_array(auth()->user()->role_id, [1, 20]);

    if ($isAdminDmcMode && $dmcSelected) {
        $noteText = filled($dmcCurrency)
            ? "* For The Selected DMC Currency is {$dmcCurrency}"
            : '* For The Selected DMC Currency is not set';
    } elseif (filled($userCurrency)) {
        $noteText = "* You have set your currency in {$userCurrency}";
    } else {
        $noteText = '* Currency has not been set for the current user';
    }
@endphp

<small
    class="currency-price-note"
    data-admin-dmc-mode="{{ $isAdminDmcMode ? '1' : '0' }}"
    data-user-currency="{{ $userCurrency }}"
    style="color: #8B0000; font-size: 0.65rem; line-height: 1.2;"
>
    <span class="currency-price-note-text">{{ $noteText }}</span>
</small>

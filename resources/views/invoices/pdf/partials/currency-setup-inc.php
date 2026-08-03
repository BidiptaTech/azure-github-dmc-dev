<?php

/**
 * Invoice PDF currency helpers — must be required from parent Blade @php scope (not @include).
 *
 * @var \App\Models\Invoice $invoice
 * @var string|null $baseCurrency
 * @var string|null $selectedCurrency
 * @var array<string, float>|null $currencyConversion
 * @var float|null $exchangeRate
 */

use App\Helpers\CommonHelper;

$baseCurrency = $baseCurrency ?? CommonHelper::resolveInvoiceBaseCurrency($invoice);
$baseCc = $baseCurrency;
$selectedCurrency = CommonHelper::getInvoiceSelectedCurrency($selectedCurrency ?? null, $invoice);

if (empty($currencyConversion)) {
    $currencyConversion = CommonHelper::buildInvoiceCurrencyConversion($invoice, $selectedCurrency);
}

$exchangeRate = $exchangeRate ?? CommonHelper::getInvoiceExchangeRate($baseCurrency, $selectedCurrency, $currencyConversion);
$showCurrencyConversion = CommonHelper::shouldShowInvoiceCurrencyConversion($baseCurrency, $selectedCurrency, $currencyConversion);
$selectedCurrencyPrefix = $selectedCurrency . ' ';

$isThirdPartyInvoice = CommonHelper::isInvoiceThirdPartyEnabled($invoice);
$invoiceMultiGeo = CommonHelper::detectInvoiceMultiGeo($invoice);
$isMultiGeoBooking = !empty($invoiceMultiGeo['is_multi']);
$showMultiGeoThirdPartyNotice = $isMultiGeoBooking && !$isThirdPartyInvoice;

if ($isThirdPartyInvoice) {
    CommonHelper::enrichInvoiceItemsWithOrderGeo($invoice);
}

$formatPrice = function ($amount, $itemOrCurrency = null) use ($baseCurrency, $selectedCurrency, $exchangeRate, $isThirdPartyInvoice) {
    if (!$isThirdPartyInvoice) {
        return CommonHelper::formatInvoiceDualPrice($amount, $baseCurrency, $selectedCurrency, $exchangeRate);
    }

    // Summary totals already converted into selected currency
    if ($itemOrCurrency === null) {
        $amt = is_numeric($amount) ? (float) $amount : 0.0;

        return CommonHelper::formatMoneyAdaptive($amt) . ' ' . strtoupper($selectedCurrency);
    }

    if (is_object($itemOrCurrency) || is_array($itemOrCurrency)) {
        $itemCurrency = CommonHelper::resolveInvoiceItemCurrency($itemOrCurrency, $baseCurrency);
    } else {
        $itemCurrency = strtoupper(trim((string) $itemOrCurrency)) ?: $baseCurrency;
    }

    return CommonHelper::formatInvoiceItemPrice($amount, $itemCurrency, $selectedCurrency, $baseCurrency);
};

$formatItemPrice = $formatPrice;

$groupItemsByGeo = function ($items) use ($baseCurrency, $isThirdPartyInvoice) {
    if (!$isThirdPartyInvoice) {
        return [[
            'key' => 'all',
            'country' => '',
            'city' => '',
            'currency' => $baseCurrency,
            'items' => $items instanceof \Illuminate\Support\Collection ? $items : collect($items),
        ]];
    }

    return CommonHelper::groupInvoiceItemsByGeo($items, $baseCurrency);
};

$serviceSectionTitle = function (string $singular, string $plural, array $geoGroup = []) use ($isThirdPartyInvoice) {
    return CommonHelper::invoiceServiceSectionTitle($singular, $plural, $geoGroup, $isThirdPartyInvoice);
};

$priceColumnSuffix = function () use ($isThirdPartyInvoice, $baseCurrency, $selectedCurrency) {
    if ($isThirdPartyInvoice) {
        return '';
    }

    $suffix = ' (' . $baseCurrency;
    if ($selectedCurrency !== $baseCurrency) {
        $suffix .= ' / ' . $selectedCurrency;
    }

    return $suffix . ')';
};

$thirdPartyNegotiation = $isThirdPartyInvoice
    ? CommonHelper::sumNegotiationDetailsInCurrency($invoice, $selectedCurrency, $baseCurrency)
    : null;

/**
 * Keep Currency Conversion box in sync with the Final / Outstanding amount shown above it.
 */
$syncInvoiceCurrencyConversion = function ($displayAmountInSelected) use (&$currencyConversion, &$showCurrencyConversion, $baseCurrency, $selectedCurrency) {
    $amt = is_numeric($displayAmountInSelected) ? (float) $displayAmountInSelected : 0.0;
    $currencyConversion = CommonHelper::buildInvoiceCurrencyConversionFromAmount(
        $amt,
        $selectedCurrency,
        $baseCurrency,
        $selectedCurrency
    );
    $showCurrencyConversion = CommonHelper::shouldShowInvoiceCurrencyConversion(
        $baseCurrency,
        $selectedCurrency,
        $currencyConversion
    );
};

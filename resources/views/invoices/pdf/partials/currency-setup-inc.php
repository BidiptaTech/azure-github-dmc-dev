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

$formatPrice = function ($amount) use ($baseCurrency, $selectedCurrency, $exchangeRate) {
    return CommonHelper::formatInvoiceDualPrice($amount, $baseCurrency, $selectedCurrency, $exchangeRate);
};

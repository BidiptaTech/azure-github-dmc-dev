<?php

namespace App\Services\Invoices;

use RuntimeException;

/**
 * The resolver selected the system invoice template (Blade).
 * InvoiceTemplateRenderer does not execute Blade.
 */
class SystemInvoiceTemplateFallbackException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $resolvedTemplate
     */
    public function __construct(private array $resolvedTemplate)
    {
        $view = (string) ($resolvedTemplate['default_view'] ?? '');

        parent::__construct(
            'System invoice templates are not rendered by InvoiceTemplateRenderer. '
            .'Use the existing Blade PDF flow'
            .($view !== '' ? ' ('.$view.')' : '')
            .'.'
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function resolvedTemplate(): array
    {
        return $this->resolvedTemplate;
    }

    public function defaultView(): string
    {
        return (string) ($this->resolvedTemplate['default_view'] ?? '');
    }
}

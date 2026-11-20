<?php

namespace App\Services\Quotations;

use RuntimeException;

/**
 * The resolver selected the system quotation template.
 *
 * QuotationTemplateRenderer does not execute Blade and does not convert
 * Blade syntax into Twig. Existing PDF generation (CommonHelper / the
 * default_view) remains the way to render system quotations.
 */
class SystemQuotationTemplateFallbackException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $resolvedTemplate
     */
    public function __construct(private array $resolvedTemplate)
    {
        $view = (string) ($resolvedTemplate['default_view'] ?? '');

        parent::__construct(
            'System quotation templates are not rendered by QuotationTemplateRenderer. '
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

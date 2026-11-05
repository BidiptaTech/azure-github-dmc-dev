<?php

namespace Tests\Unit\Invoices;

use App\Services\Invoices\InvoiceTemplateRenderer;
use App\Services\Invoices\InvoiceTemplateResolver;
use Tests\TestCase;

class InvoiceTemplateRendererTest extends TestCase
{
    public function test_renders_sandboxed_invoice_template(): void
    {
        $renderer = app(InvoiceTemplateRenderer::class);

        $resolved = [
            'source' => InvoiceTemplateResolver::SOURCE_CUSTOM,
            'dmc_id' => 1,
            'invoice_type' => 'proforma',
            'template_id' => 1,
            'version_id' => 1,
            'version_number' => 1,
            'html_content' => '<div class="wrap"><h1>{{ branding.company_name|default("DMC") }}</h1><p>{{ invoice.number|default("") }}</p><p>{{ pricing.total_amount|default(0)|number_format(2) }}</p></div>',
            'css_content' => '.wrap{color:#111;font-size:12px}',
            'identifier' => 'invoice-template:1:proforma:1',
            'default_view' => 'invoices.pdf.proforma',
        ];

        $html = $renderer->render($resolved, [
            'invoice' => ['number' => 'PRO-100', 'type' => 'proforma'],
            'branding' => ['company_name' => 'Acme Tours'],
            'pricing' => ['total_amount' => 1234.5],
            'items' => [],
            'client' => [],
            'travel_company' => [],
            'travel' => [],
            'bank_details' => [],
            'payment_terms' => [],
            'notes' => [],
            'tour' => [],
            'terms_and_conditions' => null,
        ], [
            'logoType' => 'dmc',
            'mode' => 'full',
        ]);

        $this->assertStringContainsString('Acme Tours', $html);
        $this->assertStringContainsString('PRO-100', $html);
        $this->assertStringContainsString('1,234.50', $html);
        $this->assertStringContainsString('.wrap', $html);
    }
}

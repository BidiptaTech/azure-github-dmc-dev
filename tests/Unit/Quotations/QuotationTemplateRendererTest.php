<?php

namespace Tests\Unit\Quotations;

use App\Services\Quotations\QuotationTemplateCssSanitizer;
use App\Services\Quotations\QuotationTemplateHtmlSanitizer;
use App\Services\Quotations\QuotationTemplateRenderException;
use App\Services\Quotations\QuotationTemplateRenderer;
use App\Services\Quotations\QuotationTemplateResolver;
use App\Services\Quotations\SystemQuotationTemplateFallbackException;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class QuotationTemplateRendererTest extends TestCase
{
    private QuotationTemplateRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $css = new QuotationTemplateCssSanitizer();
        $this->renderer = new QuotationTemplateRenderer($css, new QuotationTemplateHtmlSanitizer($css));
    }

    public function test_simple_variable_interpolation(): void
    {
        $html = $this->renderer->render(
            $this->customTemplate('<p>Hello {{ branding.company_name }}</p>'),
            $this->quotationData()
        );

        $this->assertStringContainsString('Hello Acme DMC', $html);
        $this->assertStringNotContainsString('{{', $html);
    }

    public function test_nested_quotation_data_access(): void
    {
        $html = $this->renderer->render(
            $this->customTemplate(
                '<p>{{ booking.lead_guest_name }} — {{ quotation.reference }} — {{ pricing.grand_total }} {{ pricing.display_currency }}</p>'
                .'<p>{{ services.by_type.hotel.0.name }}</p>'
            ),
            $this->quotationData()
        );

        $this->assertStringContainsString('Jane Doe', $html);
        $this->assertStringContainsString('DMC-100', $html);
        $this->assertStringContainsString('1234.5', $html);
        $this->assertStringContainsString('USD', $html);
        $this->assertStringContainsString('Grand Hotel', $html);
    }

    public function test_autoescaping_of_untrusted_values(): void
    {
        $data = $this->quotationData();
        $data['booking']['lead_guest_name'] = 'Jane <script>alert(1)</script>';

        $html = $this->renderer->render(
            $this->customTemplate('<p>{{ booking.lead_guest_name }}</p>'),
            $data
        );

        $this->assertStringContainsString('Jane &lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function test_allowed_template_tags_and_filters(): void
    {
        $html = $this->renderer->render(
            $this->customTemplate(
                '{% set label = booking.lead_guest_name|upper %}'
                .'{% if pricing.grand_total > 0 %}'
                .'<p>{{ label }} ({{ quotation.currency|default("USD") }})</p>'
                .'{% endif %}'
                .'<ul>{% for hotel in services.by_type.hotel %}<li>{{ hotel.name }}</li>{% endfor %}</ul>'
            ),
            $this->quotationData()
        );

        $this->assertStringContainsString('JANE DOE', $html);
        $this->assertStringContainsString('Grand Hotel', $html);
        $this->assertStringContainsString('USD', $html);
    }

    public function test_disallowed_twig_include_tag_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('Disallowed template operation');

        $this->renderer->render(
            $this->customTemplate('{% include "quotation_template" %}'),
            $this->quotationData()
        );
    }

    public function test_disallowed_twig_raw_filter_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('Disallowed template operation');

        $this->renderer->render(
            $this->customTemplate('{{ booking.lead_guest_name|raw }}'),
            $this->quotationData()
        );
    }

    public function test_disallowed_twig_function_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);

        $this->renderer->render(
            $this->customTemplate('{{ dump(booking) }}'),
            $this->quotationData()
        );
    }

    public function test_arbitrary_php_execution_is_impossible(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);

        $this->renderer->render(
            $this->customTemplate("{{ system('echo pwned') }}"),
            $this->quotationData()
        );
    }

    public function test_constant_function_cannot_read_php(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);

        $this->renderer->render(
            $this->customTemplate("{{ constant('PHP_VERSION') }}"),
            $this->quotationData()
        );
    }

    public function test_eloquent_models_are_not_exposed(): void
    {
        $model = new class extends Model
        {
            protected $guarded = [];
        };
        $model->secret = 'should-not-leak';
        $model->setAttribute('secret', 'should-not-leak');

        $data = $this->quotationData();
        $data['services']['orders'] = $model;
        $data['branding']['company_name'] = $model;

        $html = $this->renderer->render(
            $this->customTemplate(
                '<p>company=[{{ branding.company_name }}]</p>'
                .'<p>keys={{ services|keys|join(",") }}</p>'
            ),
            $data
        );

        $this->assertStringContainsString('company=[]', $html);
        $this->assertStringNotContainsString('should-not-leak', $html);
        $this->assertStringNotContainsString('orders', $html);
    }

    public function test_eloquent_model_property_access_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);

        $model = new class extends Model
        {
            protected $guarded = [];
        };
        $model->setAttribute('secret', 'should-not-leak');

        $data = $this->quotationData();
        $data['services']['orders'] = collect([$model]);

        $this->renderer->render(
            $this->customTemplate('{{ services.orders.0.secret }}'),
            $data
        );
    }

    public function test_script_and_event_handler_are_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('not allowed');

        $this->renderer->render(
            $this->customTemplate('<div onclick="alert(1)"><script>alert(1)</script>Hi</div>'),
            $this->quotationData()
        );
    }

    public function test_javascript_url_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);

        $this->renderer->render(
            $this->customTemplate('<img src="javascript:alert(1)" alt="x">'),
            $this->quotationData()
        );
    }

    public function test_external_image_url_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('Image sources');

        $this->renderer->render(
            $this->customTemplate('<img src="https://evil.example/logo.png" alt="x">'),
            $this->quotationData()
        );
    }

    public function test_dangerous_css_import_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('@import');

        $this->renderer->render(
            $this->customTemplate('<p>Hi</p>', '@import url("https://evil.example/x.css"); p { color: #000; }'),
            $this->quotationData()
        );
    }

    public function test_dangerous_css_url_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('url()');

        $this->renderer->render(
            $this->customTemplate('<p>Hi</p>', 'body { background: url("https://evil.example/bg.png"); }'),
            $this->quotationData()
        );
    }

    public function test_dangerous_css_expression_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);

        $this->renderer->render(
            $this->customTemplate('<p>Hi</p>', 'div { width: expression(alert(1)); }'),
            $this->quotationData()
        );
    }

    public function test_valid_quotation_html_and_css_rendering(): void
    {
        $template = <<<'HTML'
<div class="header">
  <h1>{{ branding.company_name }}</h1>
  <p>{{ booking.lead_guest_name }}</p>
  <table>
    <thead>
      <tr><th>Reference</th><th>Total</th></tr>
    </thead>
    <tbody>
      <tr>
        <td>{{ quotation.reference }}</td>
        <td>{{ pricing.grand_total }} {{ pricing.display_currency }}</td>
      </tr>
    </tbody>
  </table>
</div>
HTML;

        $css = <<<'CSS'
.header { margin: 16px; color: #222; }
table { width: 100%; border-collapse: collapse; }
h1 { font-size: 18px; page-break-after: avoid; }
@media print { .header { padding: 8px; } }
@page { margin: 15mm; }
CSS;

        $html = $this->renderer->render($this->customTemplate($template, $css), $this->quotationData());

        $this->assertStringContainsString('Acme DMC', $html);
        $this->assertStringContainsString('Jane Doe', $html);
        $this->assertStringContainsString('DMC-100', $html);
        $this->assertStringContainsString('1234.5', $html);
        $this->assertStringContainsString('border-collapse', $html);
        $this->assertStringContainsString('page-break-after', $html);
        $this->assertStringContainsString('@media print', $html);
        $this->assertStringContainsString('@page', $html);
        $this->assertStringContainsString('<table', $html);
    }

    public function test_empty_html_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('empty');

        $this->renderer->render(
            $this->customTemplate('   '),
            $this->quotationData()
        );
    }

    public function test_empty_rendered_html_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('empty');

        $this->renderer->render(
            $this->customTemplate('{% set unused = "x" %}'),
            $this->quotationData()
        );
    }

    public function test_invalid_twig_syntax_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('Invalid template syntax');

        $this->renderer->render(
            $this->customTemplate('{{ booking.lead_guest_name '),
            $this->quotationData()
        );
    }

    public function test_missing_template_variable_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('Template rendering failed');

        $this->renderer->render(
            $this->customTemplate('<p>{{ booking.unknown_field }}</p>'),
            $this->quotationData()
        );
    }

    public function test_system_fallback_is_not_executed_as_blade(): void
    {
        $resolved = [
            'source' => QuotationTemplateResolver::SOURCE_SYSTEM,
            'dmc_id' => 9,
            'quotation_type' => 'itinerary',
            'template_id' => null,
            'version_id' => null,
            'version_number' => null,
            'html_content' => '@php echo "blade"; @endphp <h1>{{ $tour->name }}</h1>',
            'css_content' => null,
            'identifier' => 'quotation-template:9:itinerary:system',
            'default_view' => 'single-tour-package.quotation',
        ];

        try {
            $this->renderer->render($resolved, $this->quotationData());
            $this->fail('System fallback must not be rendered.');
        } catch (SystemQuotationTemplateFallbackException $exception) {
            $this->assertSame('single-tour-package.quotation', $exception->defaultView());
            $this->assertSame($resolved['identifier'], $exception->resolvedTemplate()['identifier']);
            $this->assertStringContainsString('existing Blade PDF flow', $exception->getMessage());
            $this->assertStringNotContainsString('@php', $exception->getMessage());
            $this->assertStringNotContainsString('$tour', $exception->getMessage());
        }
    }

    public function test_invalid_template_source_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('Invalid template source');

        $resolved = $this->customTemplate('<p>Hi</p>');
        $resolved['source'] = 'unknown';

        $this->renderer->render($resolved, $this->quotationData());
    }

    public function test_render_output_is_a_complete_html_document(): void
    {
        $html = $this->renderer->render(
            $this->customTemplate('<p>{{ branding.company_name }}</p>'),
            $this->quotationData()
        );

        $this->assertStringStartsWith('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('<html>', $html);
        $this->assertStringContainsString('<head>', $html);
        $this->assertStringContainsString('<meta charset="UTF-8">', $html);
        $this->assertStringContainsString('<title>', $html);
        $this->assertStringContainsString('<body>', $html);
        $this->assertStringContainsString('</body>', $html);
        $this->assertStringContainsString('</html>', $html);
        $this->assertStringContainsString('Acme DMC', $html);
    }

    public function test_safe_data_image_is_allowed(): void
    {
        $src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        $html = $this->renderer->render(
            $this->customTemplate('<img src="'.$src.'" alt="logo" width="1" height="1">'),
            $this->quotationData()
        );

        $this->assertStringContainsString('<img', $html);
        $this->assertStringContainsString('data:image/png;base64,', $html);
    }

    public function test_extends_tag_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);

        $this->renderer->render(
            $this->customTemplate('{% extends "missing_parent" %}'),
            $this->quotationData()
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function customTemplate(string $html, string $css = ''): array
    {
        return [
            'source' => QuotationTemplateResolver::SOURCE_CUSTOM,
            'dmc_id' => 1,
            'quotation_type' => 'itinerary',
            'template_id' => 10,
            'version_id' => 20,
            'version_number' => 1,
            'html_content' => $html,
            'css_content' => $css,
            'identifier' => 'quotation-template:1:itinerary:20',
            'default_view' => 'single-tour-package.quotation',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function quotationData(): array
    {
        return [
            'quotation' => [
                'reference' => 'DMC-100',
                'currency' => 'USD',
                'type' => 'itinerary',
            ],
            'branding' => [
                'company_name' => 'Acme DMC',
                'logo' => null,
            ],
            'booking' => [
                'lead_guest_name' => 'Jane Doe',
                'email' => 'jane@example.com',
            ],
            'travel' => [
                'destination' => 'Singapore',
            ],
            'services' => [
                'by_type' => [
                    'hotel' => [
                        ['name' => 'Grand Hotel'],
                    ],
                ],
            ],
            'pricing' => [
                'grand_total' => 1234.5,
                'display_currency' => 'USD',
            ],
            'bank_details' => [],
            'terms_and_conditions' => 'No refunds',
            'exclusions' => null,
            'quotation_information' => null,
        ];
    }
}

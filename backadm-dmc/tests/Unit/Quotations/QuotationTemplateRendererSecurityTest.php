<?php

namespace Tests\Unit\Quotations;

use App\Services\Quotations\QuotationTemplateCssSanitizer;
use App\Services\Quotations\QuotationTemplateHtmlSanitizer;
use App\Services\Quotations\QuotationTemplateRenderException;
use App\Services\Quotations\QuotationTemplateRenderer;
use App\Services\Quotations\QuotationTemplateResolver;
use App\Services\Quotations\QuotationTwigSecurityPolicy;
use DOMDocument;
use DOMElement;
use DOMXPath;
use ErrorException;
use Illuminate\Contracts\Support\Arrayable;
use Twig\Sandbox\SecurityNotAllowedMethodError;
use Twig\Sandbox\SecurityNotAllowedPropertyError;
use Tests\TestCase;

class QuotationTemplateRendererSecurityTest extends TestCase
{
    private const PNG_1X1 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    private const GIF_1X1 = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    private QuotationTemplateRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $css = new QuotationTemplateCssSanitizer();
        $this->renderer = new QuotationTemplateRenderer($css, new QuotationTemplateHtmlSanitizer($css));
    }

    public function test_css_style_script_breakout_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('style element');

        $this->renderer->render(
            $this->customTemplate('<p>Hi</p>', 'p { font-family: "</style><script>alert(1)</script>"; }'),
            $this->quotationData()
        );
    }

    public function test_mixed_case_style_termination_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('style element');

        $this->renderer->render(
            $this->customTemplate('<p>Hi</p>', 'p { font-family: "</STYLE><SCRIPT>alert(1)</SCRIPT>"; }'),
            $this->quotationData()
        );
    }

    public function test_css_breakout_with_whitespace_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('style element');

        $this->renderer->render(
            $this->customTemplate('<p>Hi</p>', "p { font-family: \"</ style >\\n<script>alert(1)</script>\"; }"),
            $this->quotationData()
        );
    }

    public function test_css_breakout_with_hex_escape_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('style element');

        $this->renderer->render(
            $this->customTemplate('<p>Hi</p>', 'p { font-family: "\\3c /style>"; }'),
            $this->quotationData()
        );
    }

    public function test_final_wrapped_html_contains_no_forbidden_elements(): void
    {
        $html = $this->renderer->render(
            $this->customTemplate(
                '<div class="quote"><p>{{ branding.company_name }}</p><span>Safe</span></div>',
                'p > span { color: #111; } .quote { margin: 8px; }'
            ),
            $this->quotationData()
        );

        $this->assertStringStartsWith('<!DOCTYPE html>', $html);
        $this->assertDoesNotMatchRegularExpression('/<(script|iframe|object|embed)\b/i', $html);
        $this->assertStringNotContainsString('<script', strtolower($html));
    }

    public function test_final_wrapped_html_contains_no_event_handler_attributes(): void
    {
        $html = $this->renderer->render(
            $this->customTemplate('<p class="lead">{{ booking.lead_guest_name }}</p>'),
            $this->quotationData()
        );

        $document = new DOMDocument();
        $document->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR);
        $xpath = new DOMXPath($document);
        foreach ($xpath->query('//*') as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }
            foreach (iterator_to_array($node->attributes ?? []) as $attribute) {
                $this->assertFalse(
                    str_starts_with(strtolower($attribute->name), 'on'),
                    'Event-handler attribute '.$attribute->name.' leaked into the final document.'
                );
            }
        }
    }

    public function test_htmlpurifier_exceptions_are_wrapped(): void
    {
        $css = new QuotationTemplateCssSanitizer();
        $htmlSanitizer = new class($css) extends QuotationTemplateHtmlSanitizer
        {
            protected function purifyFragment(string $html): string
            {
                throw new ErrorException("Style attribute 'position' is not supported");
            }
        };
        $renderer = new QuotationTemplateRenderer($css, $htmlSanitizer);

        try {
            $renderer->render($this->customTemplate('<p>Hi</p>'), $this->quotationData());
            $this->fail('HTMLPurifier errors must be wrapped.');
        } catch (QuotationTemplateRenderException $exception) {
            $this->assertStringContainsString('HTML sanitization failed', $exception->getMessage());
            $this->assertInstanceOf(ErrorException::class, $exception->getPrevious());
        }
    }

    public function test_css_parser_exceptions_are_wrapped(): void
    {
        try {
            $this->renderer->render(
                $this->customTemplate('<p>Hi</p>', 'p { color: '),
                $this->quotationData()
            );
            $this->fail('Invalid CSS must be wrapped.');
        } catch (QuotationTemplateRenderException $exception) {
            $this->assertStringContainsString('Invalid CSS', $exception->getMessage());
            $this->assertNotNull($exception->getPrevious());
        }
    }

    public function test_invalid_base64_image_data_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('base64');

        $this->renderer->render(
            $this->customTemplate('<img src="data:image/png;base64,abc" alt="x"><p>Hi</p>'),
            $this->quotationData()
        );
    }

    public function test_valid_base64_that_is_not_an_image_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('valid image');

        $this->renderer->render(
            $this->customTemplate('<img src="data:image/png;base64,Zm9vYmFyYmF6" alt="x"><p>Hi</p>'),
            $this->quotationData()
        );
    }

    public function test_mime_type_and_image_byte_mismatch_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('MIME type');

        $this->renderer->render(
            $this->customTemplate('<img src="data:image/png;base64,'.self::GIF_1X1.'" alt="x"><p>Hi</p>'),
            $this->quotationData()
        );
    }

    public function test_valid_png_jpeg_and_gif_data_uris_are_allowed(): void
    {
        $jpeg = $this->jpegDataUri();

        $html = $this->renderer->render(
            $this->customTemplate(
                '<div>'
                .'<img src="data:image/png;base64,'.self::PNG_1X1.'" alt="png" width="1" height="1">'
                .'<img src="'.$jpeg.'" alt="jpeg" width="1" height="1">'
                .'<img src="data:image/gif;base64,'.self::GIF_1X1.'" alt="gif" width="1" height="1">'
                .'</div>'
            ),
            $this->quotationData()
        );

        $this->assertSame(3, substr_count($html, '<img'));
        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertStringContainsString('data:image/jpeg;base64,', $html);
        $this->assertStringContainsString('data:image/gif;base64,', $html);
    }

    public function test_oversized_image_data_uri_is_rejected(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);
        $this->expectExceptionMessage('maximum allowed size');

        $payload = base64_encode(str_repeat('A', 524289));
        $this->renderer->render(
            $this->customTemplate('<img src="data:image/png;base64,'.$payload.'" alt="x"><p>Hi</p>'),
            $this->quotationData()
        );
    }

    public function test_unknown_arrayable_dto_secret_is_not_exposed(): void
    {
        $dto = new class implements Arrayable
        {
            public function toArray(): array
            {
                return [
                    'company_name' => 'Visible',
                    'api_token' => 'SECRET-TOKEN',
                ];
            }
        };

        $data = $this->quotationData();
        $data['branding'] = $dto;

        try {
            $html = $this->renderer->render(
                $this->customTemplate('<p>{{ branding.company_name }} {{ branding.api_token }}</p>'),
                $data
            );
        } catch (QuotationTemplateRenderException $exception) {
            $this->assertStringNotContainsString('SECRET-TOKEN', $exception->getMessage());

            return;
        }

        $this->assertStringNotContainsString('SECRET-TOKEN', $html);
        $this->assertStringNotContainsString('Visible', $html);
    }

    public function test_deeply_nested_quotation_data_is_capped(): void
    {
        $nested = ['v' => 'leaf'];
        for ($i = 0; $i < 30; $i++) {
            $nested = ['child' => $nested];
        }

        $data = $this->quotationData();
        $data['pricing']['deep'] = $nested;

        $html = $this->renderer->render(
            $this->customTemplate('<p>{{ pricing.deep.child.child is defined }}</p>'),
            $data
        );

        $this->assertStringContainsString('<p>1</p>', $html);
        $this->assertStringNotContainsString('leaf', $html);
    }

    public function test_recursive_array_data_does_not_exhaust_the_renderer(): void
    {
        $cycle = ['name' => 'safe'];
        $cycle['self'] = &$cycle;

        $data = $this->quotationData();
        $data['pricing']['cycle'] = $cycle;

        $html = $this->renderer->render(
            $this->customTemplate('<p>{{ branding.company_name }}</p>'),
            $data
        );

        $this->assertStringContainsString('Acme DMC', $html);
        $this->assertStringNotContainsString('<script', strtolower($html));
    }

    public function test_normal_nested_array_access_still_works(): void
    {
        $html = $this->renderer->render(
            $this->customTemplate('<p>{{ services.by_type.hotel.0.name }}</p>'),
            $this->quotationData()
        );

        $this->assertStringContainsString('Grand Hotel', $html);
    }

    public function test_twig_object_method_and_property_access_remain_blocked(): void
    {
        $policy = new QuotationTwigSecurityPolicy(['if', 'for', 'set'], ['escape', 'e'], ['min', 'max']);

        try {
            $policy->checkMethodAllowed(new \stdClass(), 'evil');
            $this->fail('Object methods must be denied.');
        } catch (SecurityNotAllowedMethodError $exception) {
            $this->assertStringContainsString('evil', $exception->getMessage());
        }

        try {
            $policy->checkPropertyAllowed(new \stdClass(), 'secret');
            $this->fail('Object properties must be denied.');
        } catch (SecurityNotAllowedPropertyError $exception) {
            $this->assertStringContainsString('secret', $exception->getMessage());
        }
    }

    public function test_attribute_function_on_arrays_matches_key_access(): void
    {
        $html = $this->renderer->render(
            $this->customTemplate('<p>{{ attribute(branding, "company_name") }}</p>'),
            $this->quotationData()
        );

        $this->assertStringContainsString('Acme DMC', $html);
    }

    public function test_attribute_function_cannot_access_object_methods(): void
    {
        $this->expectException(QuotationTemplateRenderException::class);

        $this->renderer->render(
            $this->customTemplate('<p>{{ attribute(_self, "getTemplateName") }}</p>'),
            $this->quotationData()
        );
    }

    public function test_child_combinator_css_is_still_allowed(): void
    {
        $html = $this->renderer->render(
            $this->customTemplate('<div><p>Hi</p></div>', 'div > p { margin: 0; }'),
            $this->quotationData()
        );

        $this->assertStringContainsString('Hi', $html);
        $this->assertStringContainsString('div>p', str_replace(' ', '', $html));
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

    private function jpegDataUri(): string
    {
        $image = imagecreatetruecolor(1, 1);
        ob_start();
        imagejpeg($image, null, 80);
        $bytes = (string) ob_get_clean();
        imagedestroy($image);

        return 'data:image/jpeg;base64,'.base64_encode($bytes);
    }
}

<?php

namespace App\Services\Invoices;

use App\Services\Quotations\QuotationTemplateCssSanitizer;
use App\Services\Quotations\QuotationTemplateHtmlSanitizer;
use App\Services\Quotations\QuotationTwigSecurityPolicy;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Enumerable;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\SandboxExtension;
use Twig\Loader\ArrayLoader;
use Twig\Sandbox\SecurityError;

/**
 * Renders a resolver-selected custom invoice template to a complete HTML document.
 *
 * Reuses quotation Twig security policy + HTML/CSS sanitizers.
 *
 * Variables (plain arrays / scalars only):
 *   invoice, branding, client, travel_company, travel, items, pricing,
 *   bank_details, payment_terms, terms_and_conditions, notes, tour,
 *   presentation, template
 */
class InvoiceTemplateRenderer
{
    public const TEMPLATE_NAME = 'invoice_template';

    /**
     * @var list<string>
     */
    private const ARRAY_DATA_KEYS = [
        'invoice',
        'branding',
        'client',
        'travel_company',
        'travel',
        'items',
        'services',
        'pricing',
        'bank_details',
        'payment_terms',
        'notes',
        'tour',
    ];

    /**
     * @var list<string>
     */
    private const SCALAR_DATA_KEYS = [
        'terms_and_conditions',
    ];

    /**
     * @var list<string>
     */
    private const ALLOWED_TAGS = ['if', 'for', 'set'];

    /**
     * @var list<string>
     */
    private const ALLOWED_FILTERS = [
        'escape', 'e', 'default', 'abs', 'capitalize', 'date', 'first', 'last',
        'join', 'length', 'lower', 'upper', 'trim', 'nl2br', 'replace', 'round',
        'slice', 'sort', 'keys', 'batch', 'column', 'number_format', 'merge',
        'title', 'striptags',
    ];

    /**
     * @var list<string>
     */
    private const ALLOWED_FUNCTIONS = ['min', 'max', 'range', 'date'];

    private const MAX_DATA_DEPTH = 20;

    private const MAX_DATA_NODES = 8000;

    public function __construct(
        private QuotationTemplateCssSanitizer $cssSanitizer,
        private QuotationTemplateHtmlSanitizer $htmlSanitizer
    ) {
    }

    /**
     * @param  array<string, mixed>  $resolvedTemplate
     * @param  array<string, mixed>  $invoiceData
     * @param  array<string, mixed>  $presentation
     */
    public function render(array $resolvedTemplate, array $invoiceData, array $presentation = []): string
    {
        $this->assertResolvedTemplate($resolvedTemplate);

        try {
            $htmlContent = trim((string) $resolvedTemplate['html_content']);
            if ($htmlContent === '') {
                throw new InvoiceTemplateRenderException('Custom template HTML is empty.');
            }

            $cssContent = $this->cssSanitizer->sanitize((string) ($resolvedTemplate['css_content'] ?? ''));
            $context = $this->buildContext($resolvedTemplate, $invoiceData, $presentation);
            $rendered = $this->renderTwig($htmlContent, $context);

            if (trim($rendered) === '') {
                throw new InvoiceTemplateRenderException('Custom template rendered empty HTML.');
            }

            $title = $this->documentTitle($context);

            return $this->htmlSanitizer->sanitize($rendered, $cssContent, $title);
        } catch (SystemInvoiceTemplateFallbackException $exception) {
            throw $exception;
        } catch (InvoiceTemplateRenderException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new InvoiceTemplateRenderException(
                'Template rendering failed: '.$exception->getMessage(),
                0,
                $exception
            );
        }
    }

    /**
     * @param  array<string, mixed>  $resolvedTemplate
     */
    private function assertResolvedTemplate(array $resolvedTemplate): void
    {
        if (! isset($resolvedTemplate['source']) || ! is_string($resolvedTemplate['source']) || $resolvedTemplate['source'] === '') {
            throw new InvoiceTemplateRenderException('Invalid resolver result: missing source.');
        }

        if ($resolvedTemplate['source'] === InvoiceTemplateResolver::SOURCE_SYSTEM) {
            throw new SystemInvoiceTemplateFallbackException($resolvedTemplate);
        }

        if ($resolvedTemplate['source'] !== InvoiceTemplateResolver::SOURCE_CUSTOM) {
            throw new InvoiceTemplateRenderException(
                'Invalid template source ['.$resolvedTemplate['source'].'].'
            );
        }

        if (! array_key_exists('html_content', $resolvedTemplate)) {
            throw new InvoiceTemplateRenderException('Invalid resolver result: missing html_content.');
        }
    }

    /**
     * @param  array<string, mixed>  $resolvedTemplate
     * @param  array<string, mixed>  $invoiceData
     * @param  array<string, mixed>  $presentation
     * @return array<string, mixed>
     */
    private function buildContext(array $resolvedTemplate, array $invoiceData, array $presentation): array
    {
        $context = [];
        $state = ['nodes' => 0];

        foreach (self::ARRAY_DATA_KEYS as $key) {
            $context[$key] = $this->toPlainValue($invoiceData[$key] ?? [], 0, $state);
            if (! is_array($context[$key])) {
                $context[$key] = [];
            }
        }

        foreach (self::SCALAR_DATA_KEYS as $key) {
            $context[$key] = $this->toPlainValue($invoiceData[$key] ?? null, 0, $state);
        }

        $context['presentation'] = $this->toPlainValue($presentation, 0, $state);
        if (! is_array($context['presentation'])) {
            $context['presentation'] = [];
        }

        $context['template'] = [
            'identifier' => (string) ($resolvedTemplate['identifier'] ?? ''),
            'invoice_type' => (string) ($resolvedTemplate['invoice_type'] ?? ''),
            'dmc_id' => $this->toPlainValue($resolvedTemplate['dmc_id'] ?? null, 0, $state),
            'version_id' => $this->toPlainValue($resolvedTemplate['version_id'] ?? null, 0, $state),
            'version_number' => $this->toPlainValue($resolvedTemplate['version_number'] ?? null, 0, $state),
        ];

        return $context;
    }

    /**
     * @param  array{nodes: int}  $state
     */
    private function toPlainValue(mixed $value, int $depth, array &$state): mixed
    {
        $state['nodes']++;
        if ($state['nodes'] > self::MAX_DATA_NODES) {
            throw new InvoiceTemplateRenderException('Invoice data is too large to render.');
        }

        if ($depth > self::MAX_DATA_DEPTH) {
            return null;
        }

        if ($value === null || is_bool($value) || is_int($value) || is_float($value) || is_string($value)) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('c');
        }

        if ($value instanceof Model
            || $value instanceof \Closure
            || $value instanceof \Illuminate\Contracts\Container\Container
            || $value instanceof \Illuminate\Http\Request
            || $value instanceof \Illuminate\Database\Query\Builder
            || $value instanceof \Illuminate\Database\Eloquent\Builder
        ) {
            return null;
        }

        if ($value instanceof Enumerable) {
            return $this->plainCollection($value, $depth, $state);
        }

        if (is_array($value)) {
            $items = [];
            foreach ($value as $key => $item) {
                if (! is_int($key) && ! is_string($key)) {
                    continue;
                }
                $items[$key] = $this->toPlainValue($item, $depth + 1, $state);
            }

            return $items;
        }

        return null;
    }

    /**
     * @param  array{nodes: int}  $state
     * @return array<int|string, mixed>
     */
    private function plainCollection(Enumerable $value, int $depth, array &$state): array
    {
        $items = [];
        foreach ($value as $key => $item) {
            if (! is_int($key) && ! is_string($key)) {
                continue;
            }
            $items[$key] = $this->toPlainValue($item, $depth + 1, $state);
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function renderTwig(string $htmlContent, array $context): string
    {
        $twig = $this->makeTwig($htmlContent);

        try {
            return $twig->render(self::TEMPLATE_NAME, $context);
        } catch (SecurityError $exception) {
            throw new InvoiceTemplateRenderException(
                'Disallowed template operation: '.$exception->getMessage(),
                0,
                $exception
            );
        } catch (SyntaxError $exception) {
            throw new InvoiceTemplateRenderException(
                'Invalid template syntax: '.$exception->getMessage(),
                0,
                $exception
            );
        } catch (RuntimeError $exception) {
            throw new InvoiceTemplateRenderException(
                'Template rendering failed: '.$exception->getMessage(),
                0,
                $exception
            );
        } catch (LoaderError $exception) {
            throw new InvoiceTemplateRenderException(
                'Template loading failed: '.$exception->getMessage(),
                0,
                $exception
            );
        } catch (InvoiceTemplateRenderException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new InvoiceTemplateRenderException(
                'Template rendering failed: '.$exception->getMessage(),
                0,
                $exception
            );
        }
    }

    private function makeTwig(string $htmlContent): Environment
    {
        $twig = new Environment(new ArrayLoader([self::TEMPLATE_NAME => $htmlContent]), [
            'autoescape' => 'html',
            'strict_variables' => true,
            'debug' => false,
            'cache' => false,
            'auto_reload' => false,
        ]);

        $policy = new QuotationTwigSecurityPolicy(
            self::ALLOWED_TAGS,
            self::ALLOWED_FILTERS,
            self::ALLOWED_FUNCTIONS
        );
        $twig->addExtension(new SandboxExtension($policy, true));

        return $twig;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function documentTitle(array $context): string
    {
        $type = strtolower((string) ($context['invoice']['type'] ?? ''));
        $number = $context['invoice']['number'] ?? null;
        $label = $type === 'proforma' ? 'Proforma Invoice' : 'Invoice';

        if (is_string($number) && trim($number) !== '') {
            return $label.' '.$number;
        }

        return $label;
    }
}

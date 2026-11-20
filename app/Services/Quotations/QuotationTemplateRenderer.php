<?php

namespace App\Services\Quotations;

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
 * Renders a resolver-selected custom quotation template to a complete HTML document.
 *
 * Template syntax (Twig, sandboxed, autoescaped, strict variables):
 *   Tags:     if, for, set
 *   Filters:  escape, e, default, abs, capitalize, date, first, last, join,
 *             length, lower, upper, trim, nl2br, replace, round, slice, sort,
 *             keys, batch, column, number_format, merge, title, striptags
 *   Functions: min, max, range, date
 *
 * Twig's attribute() compiles to the same GetAttrExpression as branding.company_name.
 * It cannot be denied independently without breaking nested array access. That is
 * acceptable because renderer context contains only plain arrays and scalars;
 * object method/property access remains blocked by the sandbox policy.
 *
 * Variables (plain arrays / scalars only):
 *   quotation, branding, booking, travel, services, pricing, bank_details,
 *   terms_and_conditions, exclusions, quotation_information, presentation, template
 *
 * System source is not rendered here. Catch SystemQuotationTemplateFallbackException
 * and use resolved default_view through the existing Blade PDF flow.
 */
class QuotationTemplateRenderer
{
    public const TEMPLATE_NAME = 'quotation_template';

    /**
     * @var list<string>
     */
    private const ARRAY_DATA_KEYS = [
        'quotation',
        'branding',
        'booking',
        'travel',
        'services',
        'pricing',
        'bank_details',
    ];

    /**
     * @var list<string>
     */
    private const SCALAR_DATA_KEYS = [
        'terms_and_conditions',
        'exclusions',
        'quotation_information',
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
     * @param  array<string, mixed>  $resolvedTemplate  Resolver result from QuotationTemplateResolver
     * @param  array<string, mixed>  $quotationData     Phase 1 normalized quotationData
     * @param  array<string, mixed>  $presentation      Explicitly approved extra presentation values
     */
    public function render(array $resolvedTemplate, array $quotationData, array $presentation = []): string
    {
        $this->assertResolvedTemplate($resolvedTemplate);

        try {
            $htmlContent = trim((string) $resolvedTemplate['html_content']);
            if ($htmlContent === '') {
                throw new QuotationTemplateRenderException('Custom template HTML is empty.');
            }

            $cssContent = $this->cssSanitizer->sanitize((string) ($resolvedTemplate['css_content'] ?? ''));
            $context = $this->buildContext($resolvedTemplate, $quotationData, $presentation);
            $rendered = $this->renderTwig($htmlContent, $context);

            if (trim($rendered) === '') {
                throw new QuotationTemplateRenderException('Custom template rendered empty HTML.');
            }

            $title = $this->documentTitle($context);

            return $this->htmlSanitizer->sanitize($rendered, $cssContent, $title);
        } catch (SystemQuotationTemplateFallbackException $exception) {
            throw $exception;
        } catch (QuotationTemplateRenderException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new QuotationTemplateRenderException(
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
            throw new QuotationTemplateRenderException('Invalid resolver result: missing source.');
        }

        if ($resolvedTemplate['source'] === QuotationTemplateResolver::SOURCE_SYSTEM) {
            throw new SystemQuotationTemplateFallbackException($resolvedTemplate);
        }

        if ($resolvedTemplate['source'] !== QuotationTemplateResolver::SOURCE_CUSTOM) {
            throw new QuotationTemplateRenderException(
                'Invalid template source ['.$resolvedTemplate['source'].'].'
            );
        }

        if (! array_key_exists('html_content', $resolvedTemplate)) {
            throw new QuotationTemplateRenderException('Invalid resolver result: missing html_content.');
        }
    }

    /**
     * @param  array<string, mixed>  $resolvedTemplate
     * @param  array<string, mixed>  $quotationData
     * @param  array<string, mixed>  $presentation
     * @return array<string, mixed>
     */
    private function buildContext(array $resolvedTemplate, array $quotationData, array $presentation): array
    {
        $context = [];

        $state = ['nodes' => 0];

        foreach (self::ARRAY_DATA_KEYS as $key) {
            $context[$key] = $this->toPlainValue($quotationData[$key] ?? [], 0, $state);
            if (! is_array($context[$key])) {
                $context[$key] = [];
            }
        }

        if (isset($context['services']) && is_array($context['services'])) {
            unset($context['services']['orders']);
        }

        foreach (self::SCALAR_DATA_KEYS as $key) {
            $context[$key] = $this->toPlainValue($quotationData[$key] ?? null, 0, $state);
        }

        $context['presentation'] = $this->toPlainValue($presentation, 0, $state);
        if (! is_array($context['presentation'])) {
            $context['presentation'] = [];
        }

        $context['template'] = [
            'identifier' => (string) ($resolvedTemplate['identifier'] ?? ''),
            'quotation_type' => (string) ($resolvedTemplate['quotation_type'] ?? ''),
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
            throw new QuotationTemplateRenderException('Quotation data is too large to render.');
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
            throw new QuotationTemplateRenderException(
                'Disallowed template operation: '.$exception->getMessage(),
                0,
                $exception
            );
        } catch (SyntaxError $exception) {
            throw new QuotationTemplateRenderException(
                'Invalid template syntax: '.$exception->getMessage(),
                0,
                $exception
            );
        } catch (RuntimeError $exception) {
            throw new QuotationTemplateRenderException(
                'Template rendering failed: '.$exception->getMessage(),
                0,
                $exception
            );
        } catch (LoaderError $exception) {
            throw new QuotationTemplateRenderException(
                'Template loading failed: '.$exception->getMessage(),
                0,
                $exception
            );
        } catch (QuotationTemplateRenderException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new QuotationTemplateRenderException(
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
        $reference = $context['quotation']['reference'] ?? null;
        if (is_string($reference) && trim($reference) !== '') {
            return 'Quotation '.$reference;
        }

        return 'Quotation';
    }
}

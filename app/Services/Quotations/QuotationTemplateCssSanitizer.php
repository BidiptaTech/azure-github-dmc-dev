<?php

namespace App\Services\Quotations;

use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\CSSList\KeyFrame;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Property\Charset;
use Sabberworm\CSS\Property\CSSNamespace;
use Sabberworm\CSS\Property\Declaration;
use Sabberworm\CSS\Property\Import;
use Sabberworm\CSS\RuleSet\AtRuleSet;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\Settings;
use Sabberworm\CSS\Value\Color;
use Sabberworm\CSS\Value\CSSFunction;
use Sabberworm\CSS\Value\CSSString;
use Sabberworm\CSS\Value\Size;
use Sabberworm\CSS\Value\URL;
use Sabberworm\CSS\Value\ValueList;

/**
 * Parses quotation CSS and rejects constructs that are unsafe for untrusted
 * templates or incompatible with DomPDF (remote loading, executable CSS).
 *
 * Unsupported / rejected:
 * - @import, @font-face, @namespace, @keyframes, @supports, unknown at-rules
 * - url(), image(), image-set(), attr(), expression(), behavior, -moz-binding
 * - javascript: / data: / remote resource references
 * - untrusted webfonts and arbitrary external resources
 */
class QuotationTemplateCssSanitizer
{
    /**
     * @var list<string>
     */
    private const ALLOWED_AT_RULES = ['media', 'page'];

    /**
     * @var list<string>
     */
    private const ALLOWED_FUNCTIONS = ['rgb', 'rgba', 'hsl', 'hsla', 'calc', 'min', 'max', 'clamp'];

    /**
     * DomPDF-oriented layout properties. Unknown properties are rejected.
     *
     * @var list<string>
     */
    private const ALLOWED_PROPERTIES = [
        'color', 'background', 'background-color',
        'font', 'font-family', 'font-size', 'font-weight', 'font-style', 'font-variant',
        'line-height', 'letter-spacing', 'word-spacing', 'text-align', 'text-decoration',
        'text-transform', 'text-indent', 'white-space', 'vertical-align', 'direction',
        'width', 'height', 'min-width', 'min-height', 'max-width', 'max-height',
        'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
        'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
        'border', 'border-top', 'border-right', 'border-bottom', 'border-left',
        'border-width', 'border-top-width', 'border-right-width', 'border-bottom-width', 'border-left-width',
        'border-style', 'border-top-style', 'border-right-style', 'border-bottom-style', 'border-left-style',
        'border-color', 'border-top-color', 'border-right-color', 'border-bottom-color', 'border-left-color',
        'border-collapse', 'border-spacing', 'border-radius',
        'table-layout', 'caption-side', 'empty-cells',
        'display', 'position', 'top', 'right', 'bottom', 'left', 'float', 'clear',
        'overflow', 'overflow-x', 'overflow-y', 'box-sizing', 'opacity', 'z-index',
        'list-style', 'list-style-type', 'list-style-position',
        'visibility', 'page-break-before', 'page-break-after', 'page-break-inside',
        'break-before', 'break-after', 'break-inside', 'orphans', 'widows', 'page', 'size',
        'word-break', 'overflow-wrap', 'word-wrap',
        'print-color-adjust', '-webkit-print-color-adjust',
    ];

    public function sanitize(string $css): string
    {
        try {
            $css = trim($css);
            if ($css === '') {
                return '';
            }

            $this->assertNoForbiddenTokens($css);
            $document = $this->parse($css);
            $this->inspectList($document);
            $rendered = trim($document->render(OutputFormat::createCompact()));
            if ($rendered !== '') {
                $this->assertNoForbiddenTokens($rendered);
            }

            return $rendered;
        } catch (QuotationTemplateRenderException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new QuotationTemplateRenderException(
                'Invalid CSS: '.$exception->getMessage(),
                0,
                $exception
            );
        }
    }

    public function validateInlineDeclarations(string $declarations): void
    {
        $declarations = trim($declarations);
        if ($declarations === '') {
            return;
        }

        $this->sanitize('inline-style-probe{'.$declarations.'}');
    }

    private function parse(string $css): Document
    {
        try {
            $parser = new Parser($css, Settings::create()->beStrict());

            return $parser->parse();
        } catch (SourceException $exception) {
            throw new QuotationTemplateRenderException(
                'Invalid CSS: '.$exception->getMessage(),
                0,
                $exception
            );
        } catch (\Throwable $exception) {
            throw new QuotationTemplateRenderException(
                'Invalid CSS: '.$exception->getMessage(),
                0,
                $exception
            );
        }
    }

    private function inspectList(CSSList $list): void
    {
        foreach ($list->getContents() as $item) {
            if ($item instanceof Import) {
                throw new QuotationTemplateRenderException('CSS @import is not allowed.');
            }

            if ($item instanceof Charset) {
                continue;
            }

            if ($item instanceof CSSNamespace || $item instanceof KeyFrame) {
                $name = $item instanceof KeyFrame ? 'keyframes' : 'namespace';
                throw new QuotationTemplateRenderException('CSS at-rule @'.$name.' is not allowed.');
            }

            if ($item instanceof AtRuleSet) {
                $this->inspectAtRuleSet($item);
                continue;
            }

            if ($item instanceof AtRuleBlockList) {
                $this->inspectAtRule($item);
                continue;
            }

            if ($item instanceof DeclarationBlock) {
                $this->inspectDeclarationBlock($item);
                continue;
            }

            if ($item instanceof CSSList) {
                $this->inspectList($item);
                continue;
            }

            throw new QuotationTemplateRenderException(
                'Unsupported CSS construct ['.get_debug_type($item).'].'
            );
        }
    }

    private function inspectAtRuleSet(AtRuleSet $rule): void
    {
        $name = strtolower($rule->atRuleName());
        if ($name !== 'page') {
            throw new QuotationTemplateRenderException('CSS at-rule @'.$name.' is not allowed.');
        }

        $this->assertNoForbiddenTokens($rule->atRuleArgs());
        foreach ($rule->getDeclarations() as $declaration) {
            $this->inspectDeclaration($declaration);
        }
    }

    private function inspectAtRule(AtRuleBlockList $rule): void
    {
        $name = strtolower($rule->atRuleName());
        if (! in_array($name, self::ALLOWED_AT_RULES, true)) {
            throw new QuotationTemplateRenderException('CSS at-rule @'.$name.' is not allowed.');
        }

        $args = strtolower($rule->atRuleArgs());
        $this->assertNoForbiddenTokens($args);
        if ($name === 'media' && $args !== '' && ! $this->isSafeMediaQuery($args)) {
            throw new QuotationTemplateRenderException('CSS @media query is not allowed.');
        }

        $this->inspectList($rule);
    }

    private function isSafeMediaQuery(string $args): bool
    {
        $normalized = preg_replace('/\s+/', ' ', trim($args)) ?? $args;
        if (in_array($normalized, ['all', 'print', 'screen', 'all and print', 'print, screen', 'screen, print'], true)) {
            return true;
        }

        // Allow simple print/screen queries without urls or expressions.
        return (bool) preg_match('/^(only\s+)?(all|print|screen)(\s+and\s+\([^)]+\))?$/i', $normalized);
    }

    private function inspectDeclarationBlock(DeclarationBlock $block): void
    {
        foreach ($block->getDeclarations() as $declaration) {
            $this->inspectDeclaration($declaration);
        }
    }

    private function inspectDeclaration(Declaration $declaration): void
    {
        $property = strtolower($declaration->getPropertyName());
        if (str_starts_with($property, '--')) {
            throw new QuotationTemplateRenderException('CSS custom properties are not allowed.');
        }

        if (! in_array($property, self::ALLOWED_PROPERTIES, true)) {
            throw new QuotationTemplateRenderException('CSS property ['.$property.'] is not allowed.');
        }

        $this->inspectValue($declaration->getValue());
    }

    private function inspectValue(mixed $value): void
    {
        if ($value === null || is_int($value) || is_float($value)) {
            return;
        }

        if ($value instanceof URL) {
            throw new QuotationTemplateRenderException('CSS url() is not allowed.');
        }

        if ($value instanceof Size) {
            return;
        }

        if ($value instanceof Color) {
            foreach ($value->getColor() as $component) {
                $this->inspectValue($component);
            }

            return;
        }

        if ($value instanceof CSSFunction) {
            $rendered = strtolower($value->render(OutputFormat::createCompact()));
            $name = str_contains($rendered, '(') ? (string) strstr($rendered, '(', true) : $rendered;
            if (! in_array($name, self::ALLOWED_FUNCTIONS, true)) {
                throw new QuotationTemplateRenderException('CSS function ['.$name.'] is not allowed.');
            }
            foreach ($value->getArguments() as $argument) {
                $this->inspectValue($argument);
            }

            return;
        }

        if ($value instanceof ValueList) {
            foreach ($value->getListComponents() as $component) {
                $this->inspectValue($component);
            }

            return;
        }

        if ($value instanceof CSSString) {
            $this->assertNoForbiddenTokens($value->getString());

            return;
        }

        if (is_string($value)) {
            $this->assertNoForbiddenTokens($value);

            return;
        }

        if (is_array($value)) {
            foreach ($value as $component) {
                $this->inspectValue($component);
            }

            return;
        }

        if (is_object($value) && method_exists($value, 'render')) {
            $this->assertNoForbiddenTokens($value->render(OutputFormat::createCompact()));
        }
    }

    private function assertNoForbiddenTokens(string $css): void
    {
        $this->assertNoHtmlBreakout($css);

        if (preg_match('/@import\b/i', $css) === 1) {
            throw new QuotationTemplateRenderException('CSS @import is not allowed.');
        }

        if (preg_match('/expression\s*\(/i', $css) === 1) {
            throw new QuotationTemplateRenderException('CSS expression() is not allowed.');
        }

        if (preg_match('/behavior\s*:/i', $css) === 1) {
            throw new QuotationTemplateRenderException('CSS behavior is not allowed.');
        }

        if (preg_match('/-moz-binding/i', $css) === 1) {
            throw new QuotationTemplateRenderException('CSS -moz-binding is not allowed.');
        }

        if (preg_match('/javascript\s*:/i', $css) === 1) {
            throw new QuotationTemplateRenderException('CSS javascript: URLs are not allowed.');
        }

        if (preg_match('/url\s*\(/i', $css) === 1) {
            throw new QuotationTemplateRenderException('CSS url() is not allowed.');
        }
    }

    /**
     * Reject CSS that can terminate a HTML <style> element or inject markup.
     * Parser-based inspection still runs; this is an additional embedding check.
     */
    private function assertNoHtmlBreakout(string $css): void
    {
        $decoded = $this->decodeCssEscapes($this->stripCssComments($css));
        $normalized = strtolower($decoded);
        $collapsed = preg_replace('/\s+/', '', $normalized) ?? $normalized;

        if (str_contains($collapsed, '<') || str_contains($collapsed, '&lt;')) {
            throw new QuotationTemplateRenderException(
                'CSS contains HTML markup that could break out of a style element.'
            );
        }
    }

    private function stripCssComments(string $css): string
    {
        return preg_replace('#/\*.*?\*/#s', '', $css) ?? $css;
    }

    private function decodeCssEscapes(string $css): string
    {
        return (string) preg_replace_callback(
            '/\\\\(?:([0-9A-Fa-f]{1,6})[ \\t\\n\\r\\f]?|([^0-9A-Fa-f\\n\\r\\f]))/',
            static function (array $match): string {
                if (($match[1] ?? '') !== '') {
                    $code = hexdec($match[1]);
                    if ($code === 0 || $code > 0x10FFFF) {
                        return '';
                    }
                    $char = mb_chr($code, 'UTF-8');

                    return is_string($char) ? $char : '';
                }

                return $match[2] ?? '';
            },
            $css
        );
    }
}

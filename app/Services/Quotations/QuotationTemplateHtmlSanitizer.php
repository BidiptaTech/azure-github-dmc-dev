<?php

namespace App\Services\Quotations;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Validates rendered quotation markup, then purifies it and wraps a complete
 * HTML document. Twig autoescaping is not treated as a substitute for this.
 */
class QuotationTemplateHtmlSanitizer
{
    /**
     * @var list<string>
     */
    private const ALLOWED_ELEMENTS = [
        'html', 'head', 'body', 'title', 'meta', 'style',
        'div', 'span', 'p',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
        'ul', 'ol', 'li',
        'strong', 'b', 'em', 'i', 'u',
        'img', 'hr', 'br',
    ];

    /**
     * @var list<string>
     */
    private const ALLOWED_ATTRIBUTES = [
        'class', 'id', 'style', 'title', 'lang', 'dir',
        'colspan', 'rowspan', 'width', 'height', 'align', 'valign',
        'src', 'alt', 'border', 'cellpadding', 'cellspacing',
        'scope', 'headers', 'charset',
    ];

    /**
     * @var list<string>
     */
    private const FORBIDDEN_ELEMENTS = [
        'script', 'iframe', 'object', 'embed', 'form', 'input',
        'button', 'textarea', 'select', 'option', 'link', 'base',
        'svg', 'math', 'video', 'audio', 'source', 'canvas',
        'applet', 'frame', 'frameset', 'noscript',
    ];

    private const MAX_DECODED_IMAGE_BYTES = 524288;

    /**
     * IMAGETYPE_* values (png=3, jpeg=2, gif=1) without requiring GD at class load.
     *
     * @var array<string, int>
     */
    private const IMAGE_MIME_TYPES = [
        'image/png' => 3,
        'image/jpeg' => 2,
        'image/jpg' => 2,
        'image/gif' => 1,
    ];

    public function __construct(private QuotationTemplateCssSanitizer $cssSanitizer)
    {
    }

    public function sanitize(string $html, string $safeCss, string $title = 'Quotation'): string
    {
        try {
            $html = trim($html);
            if ($html === '') {
                throw new QuotationTemplateRenderException('Rendered template HTML is empty.');
            }

            $parsed = $this->loadDocument($html);
            $this->assertDocumentIsSafe($parsed['document']);

            $extractedCss = $this->cssSanitizer->sanitize($parsed['css']);
            $combinedCss = trim($safeCss."\n".$extractedCss);
            if ($combinedCss !== '') {
                $combinedCss = $this->cssSanitizer->sanitize($combinedCss);
            }

            $title = $this->safeTitle($parsed['title'] !== '' ? $parsed['title'] : $title);
            $purified = $this->purifyFragment($parsed['body']);
            if (trim($purified) === '') {
                throw new QuotationTemplateRenderException('Rendered template HTML is empty after sanitization.');
            }

            $this->assertFragmentIsSafe($purified);

            $document = $this->wrapDocument($title, $combinedCss, $purified);
            $this->assertFinalDocumentIsSafe($document);

            return $document;
        } catch (QuotationTemplateRenderException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new QuotationTemplateRenderException(
                'HTML sanitization failed: '.$exception->getMessage(),
                0,
                $exception
            );
        }
    }

    /**
     * @return array{document: DOMDocument, body: string, css: string, title: string}
     */
    private function loadDocument(string $html): array
    {
        try {
            $previous = libxml_use_internal_errors(true);
            $document = new DOMDocument('1.0', 'UTF-8');
            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8">'.$html,
                LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
            );
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            if ($loaded !== true) {
                throw new QuotationTemplateRenderException('Invalid HTML: the template could not be parsed.');
            }

            return [
                'document' => $document,
                'body' => $this->extractBodyHtml($document),
                'css' => $this->extractStyleText($document),
                'title' => $this->extractTitle($document),
            ];
        } catch (QuotationTemplateRenderException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new QuotationTemplateRenderException(
                'Invalid HTML: '.$exception->getMessage(),
                0,
                $exception
            );
        }
    }

    private function extractStyleText(DOMDocument $document): string
    {
        $chunks = [];
        foreach ($document->getElementsByTagName('style') as $style) {
            $chunks[] = $style->textContent ?? '';
        }

        return trim(implode("\n", $chunks));
    }

    private function extractTitle(DOMDocument $document): string
    {
        $titles = $document->getElementsByTagName('title');
        if ($titles->length === 0) {
            return '';
        }

        return trim((string) $titles->item(0)?->textContent);
    }

    private function extractBodyHtml(DOMDocument $document): string
    {
        $bodies = $document->getElementsByTagName('body');
        $root = $bodies->item(0) ?? $document->documentElement;
        if (! $root instanceof DOMNode) {
            return '';
        }

        $html = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            if ($child instanceof DOMElement && strtolower($child->tagName) === 'style') {
                continue;
            }
            $html .= $document->saveHTML($child);
        }

        return trim($html);
    }

    private function assertDocumentIsSafe(DOMDocument $document): void
    {
        $xpath = new DOMXPath($document);
        foreach ($xpath->query('//*') as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }
            $this->assertElementIsSafe($node);
        }
    }

    private function assertFragmentIsSafe(string $html): void
    {
        $parsed = $this->loadDocument($html);
        $this->assertDocumentIsSafe($parsed['document']);
    }

    private function assertElementIsSafe(DOMElement $element): void
    {
        $tag = strtolower($element->tagName);

        if ($tag === 'meta') {
            $this->assertSafeMeta($element);

            return;
        }

        if (in_array($tag, self::FORBIDDEN_ELEMENTS, true)) {
            throw new QuotationTemplateRenderException('HTML element <'.$tag.'> is not allowed.');
        }

        if (! in_array($tag, self::ALLOWED_ELEMENTS, true)) {
            throw new QuotationTemplateRenderException('HTML element <'.$tag.'> is not allowed.');
        }

        if ($tag === 'img' && trim($element->getAttribute('src')) === '') {
            throw new QuotationTemplateRenderException('Image is missing a valid data URI source.');
        }

        foreach (iterator_to_array($element->attributes ?? []) as $attribute) {
            $name = strtolower($attribute->name);
            $value = trim((string) $attribute->value);

            if (str_starts_with($name, 'on')) {
                throw new QuotationTemplateRenderException('HTML event-handler attribute ['.$name.'] is not allowed.');
            }

            if (in_array($name, ['href', 'srcset', 'poster', 'formaction', 'xlink:href', 'xmlns:xlink'], true)) {
                throw new QuotationTemplateRenderException('HTML attribute ['.$name.'] is not allowed.');
            }

            if ($name === 'src' && $tag !== 'img') {
                throw new QuotationTemplateRenderException('HTML src is only allowed on img.');
            }

            if ($tag === 'img' && $name === 'src') {
                $this->assertSafeImageSrc($value);
            }

            if ($name === 'style') {
                $this->cssSanitizer->validateInlineDeclarations($value);
            }

            if (! in_array($name, self::ALLOWED_ATTRIBUTES, true) && $name !== 'xmlns') {
                throw new QuotationTemplateRenderException('HTML attribute ['.$name.'] is not allowed.');
            }
        }
    }

    private function assertSafeMeta(DOMElement $element): void
    {
        $charset = strtolower(trim($element->getAttribute('charset')));
        $httpEquiv = strtolower(trim($element->getAttribute('http-equiv')));
        $name = strtolower(trim($element->getAttribute('name')));

        if ($httpEquiv === 'content-type' && $name === '') {
            return;
        }

        if ($httpEquiv !== '' || $name !== '') {
            throw new QuotationTemplateRenderException('HTML meta is limited to charset UTF-8.');
        }

        if ($charset !== '' && $charset !== 'utf-8') {
            throw new QuotationTemplateRenderException('HTML meta charset must be UTF-8.');
        }
    }

    private function assertSafeImageSrc(string $src): void
    {
        $src = preg_replace('/\s+/', '', $src) ?? $src;
        if ($src === '' || ! str_starts_with(strtolower($src), 'data:')) {
            throw new QuotationTemplateRenderException(
                'Image sources must be restricted data:image URIs (png, jpeg, or gif).'
            );
        }

        if (! preg_match('#^data:([a-z0-9.+-]+/[a-z0-9.+-]+);base64,([A-Za-z0-9+/]+={0,2})$#i', $src, $matches)) {
            throw new QuotationTemplateRenderException('Image data URI is malformed.');
        }

        $mime = strtolower($matches[1]);
        $payload = $matches[2];

        if ($mime === 'image/webp') {
            throw new QuotationTemplateRenderException('WebP image data URIs are not supported.');
        }

        if (! isset(self::IMAGE_MIME_TYPES[$mime])) {
            throw new QuotationTemplateRenderException(
                'Image sources must be restricted data:image URIs (png, jpeg, or gif).'
            );
        }

        if (strlen($payload) % 4 !== 0 || preg_match('#^[A-Za-z0-9+/]+={0,2}$#', $payload) !== 1) {
            throw new QuotationTemplateRenderException('Image data URI base64 is invalid.');
        }

        $bytes = base64_decode($payload, true);
        if ($bytes === false || $bytes === '') {
            throw new QuotationTemplateRenderException('Image data URI base64 is invalid.');
        }

        if (strlen($bytes) > self::MAX_DECODED_IMAGE_BYTES) {
            throw new QuotationTemplateRenderException('Image data URI exceeds the maximum allowed size.');
        }

        $info = @getimagesizefromstring($bytes);
        if ($info === false || ! isset($info[2], $info['mime'])) {
            throw new QuotationTemplateRenderException('Image data URI does not contain a valid image.');
        }

        $expectedType = self::IMAGE_MIME_TYPES[$mime];
        $actualMime = strtolower((string) $info['mime']);
        if ((int) $info[2] !== $expectedType || ($actualMime !== $mime && ! ($mime === 'image/jpg' && $actualMime === 'image/jpeg'))) {
            throw new QuotationTemplateRenderException('Image data URI MIME type does not match the image bytes.');
        }
    }

    private function assertFinalDocumentIsSafe(string $html): void
    {
        $parsed = $this->loadDocument($html);
        $this->assertDocumentIsSafe($parsed['document']);
        if ($parsed['css'] !== '') {
            $this->cssSanitizer->sanitize($parsed['css']);
        }
    }

    /**
     * @internal Overridable in tests to verify exception wrapping.
     */
    protected function purifyFragment(string $html): string
    {
        try {
            return $this->createPurifier()->purify($html);
        } catch (QuotationTemplateRenderException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new QuotationTemplateRenderException(
                'HTML sanitization failed: '.$exception->getMessage(),
                0,
                $exception
            );
        }
    }

    private function createPurifier(): HTMLPurifier
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('Cache.DefinitionImpl', null);
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.Allowed', $this->purifierAllowed());
        $config->set('HTML.ForbiddenElements', 'script,iframe,object,embed,form,input,link,base,svg');
        $config->set('Attr.EnableID', true);
        $config->set('CSS.AllowImportant', true);
        $config->set('CSS.AllowTricky', true);
        $config->set('CSS.AllowedProperties', $this->purifierCssProperties());
        $config->set('URI.DisableExternal', true);
        $config->set('URI.DisableExternalResources', true);
        $config->set('URI.AllowedSchemes', ['data' => true]);
        $config->set('URI.OverrideAllowedSchemes', true);
        $config->set('AutoFormat.RemoveEmpty', false);

        return new HTMLPurifier($config);
    }

    private function purifierAllowed(): string
    {
        return implode(',', [
            'div[class|id|style|align]',
            'span[class|id|style]',
            'p[class|id|style|align]',
            'h1[class|id|style|align]',
            'h2[class|id|style|align]',
            'h3[class|id|style|align]',
            'h4[class|id|style|align]',
            'h5[class|id|style|align]',
            'h6[class|id|style|align]',
            'table[class|id|style|width|border|cellpadding|cellspacing|align]',
            'thead[class|id|style]',
            'tbody[class|id|style]',
            'tfoot[class|id|style]',
            'tr[class|id|style|align|valign]',
            'th[class|id|style|colspan|rowspan|width|height|align|valign|scope]',
            'td[class|id|style|colspan|rowspan|width|height|align|valign]',
            'ul[class|id|style]',
            'ol[class|id|style]',
            'li[class|id|style]',
            'strong[class|id|style]',
            'b[class|id|style]',
            'em[class|id|style]',
            'i[class|id|style]',
            'u[class|id|style]',
            'img[class|id|style|src|alt|width|height]',
            'hr[class|id|style]',
            'br',
        ]);
    }

    /**
     * @return array<string, bool>
     */
    private function purifierCssProperties(): array
    {
        // Only properties HTMLPurifier actually defines. Listing unsupported ones
        // (position, display, page-break-*, top/right/bottom/left) triggers
        // E_USER_WARNING → ErrorException under Laravel's error handler.
        // Stylesheet page-breaks / positioning stay allowed via CssSanitizer.
        $allowed = [
            'color', 'background-color', 'background',
            'font', 'font-family', 'font-size', 'font-weight', 'font-style', 'font-variant',
            'line-height', 'letter-spacing', 'word-spacing', 'text-align', 'text-decoration',
            'text-transform', 'text-indent', 'white-space', 'vertical-align',
            'width', 'height', 'min-width', 'min-height', 'max-width', 'max-height',
            'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
            'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
            'border', 'border-width', 'border-style', 'border-color',
            'border-collapse', 'border-spacing',
            'float', 'clear',
        ];

        return array_fill_keys($allowed, true);
    }

    private function safeTitle(string $title): string
    {
        $title = trim(html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $title = preg_replace('/\s+/', ' ', $title) ?? $title;
        $title = strip_tags($title);
        if ($title === '') {
            return 'Quotation';
        }

        return mb_substr($title, 0, 180);
    }

    private function wrapDocument(string $title, string $css, string $body): string
    {
        $styleBlock = '';
        if ($css !== '') {
            $styleBlock = "\n<style>\n".$css."\n</style>";
        }

        return '<!DOCTYPE html>'."\n"
            .'<html>'."\n"
            .'<head>'."\n"
            .'<meta charset="UTF-8">'."\n"
            .'<title>'.htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</title>'
            .$styleBlock."\n"
            .'</head>'."\n"
            .'<body>'."\n"
            .$body."\n"
            .'</body>'."\n"
            .'</html>';
    }
}

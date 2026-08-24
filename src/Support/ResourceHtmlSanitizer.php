<?php

namespace Azuriom\Plugin\Marketplace\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class ResourceHtmlSanitizer
{
    /** @var array<string, array<int, string>> */
    private const ALLOWED_ELEMENTS = [
        'p' => [], 'br' => [], 'h2' => [], 'h3' => [], 'h4' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'blockquote' => [], 'ul' => [], 'ol' => [], 'li' => [],
        'a' => ['href', 'title', 'target'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [],
        'th' => ['colspan', 'rowspan'], 'td' => ['colspan', 'rowspan'],
        'pre' => [], 'code' => [], 'hr' => [],
    ];

    /** @var array<int, string> */
    private const DROP_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'svg', 'math',
        'form', 'input', 'button', 'textarea', 'select', 'option',
    ];

    public function sanitize(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8" ?><div id="marketplace-content">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('marketplace-content');
        if (! $root instanceof DOMElement) {
            return '';
        }

        $this->sanitizeChildren($root);

        $result = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $result .= $document->saveHTML($child);
        }

        return trim($result);
    }

    private function sanitizeChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($node->tagName);
            if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                $node->parentNode?->removeChild($node);
                continue;
            }

            if (! array_key_exists($tag, self::ALLOWED_ELEMENTS)) {
                $this->sanitizeChildren($node);
                $this->unwrap($node);
                continue;
            }

            $this->sanitizeAttributes($node, self::ALLOWED_ELEMENTS[$tag]);
            $this->sanitizeChildren($node);
        }
    }

    /** @param array<int, string> $allowed */
    private function sanitizeAttributes(DOMElement $element, array $allowed): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            if (! in_array(strtolower($attribute->name), $allowed, true)) {
                $element->removeAttribute($attribute->name);
            }
        }

        if ($element->tagName === 'a') {
            if (! $this->isSafeUrl($element->getAttribute('href'), true)) {
                $element->removeAttribute('href');
            }

            if ($element->getAttribute('target') !== '_blank') {
                $element->removeAttribute('target');
            } else {
                $element->setAttribute('rel', 'nofollow noopener noreferrer');
            }
        }

        if ($element->tagName === 'img' && ! $this->isSafeUrl($element->getAttribute('src'))) {
            $element->parentNode?->removeChild($element);
            return;
        }

        foreach (['width', 'height', 'colspan', 'rowspan'] as $attribute) {
            if ($element->hasAttribute($attribute)
                && ! preg_match('/^[1-9][0-9]{0,3}$/', $element->getAttribute($attribute))) {
                $element->removeAttribute($attribute);
            }
        }
    }

    private function isSafeUrl(string $url, bool $allowMail = false): bool
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if ($scheme === '') {
            return str_starts_with($url, '/') && ! str_starts_with($url, '//');
        }

        return in_array($scheme, $allowMail ? ['http', 'https', 'mailto'] : ['http', 'https'], true);
    }

    private function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if ($parent === null) {
            return;
        }

        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }
}

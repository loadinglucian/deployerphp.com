<?php

declare(strict_types=1);

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;

final readonly class HeadingExtractorService
{
    /**
     * Extract headings from rendered HTML.
     *
     * @return array<int, array{level: int, text: string, id: string}>
     */
    public function extract(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        $dom = new DOMDocument;
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR
        );

        $xpath = new DOMXPath($dom);
        $headings = [];

        // Query for h2 and h3 elements (h1 is the page title, extracted separately)
        $nodes = $xpath->query('//h2 | //h3');

        if ($nodes === false) {
            return [];
        }

        foreach ($nodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $level = (int) substr($node->nodeName, 1);
            $id = $this->extractHeadingId($node);
            $text = $this->extractTextContent($node);

            if ($text === '') {
                continue;
            }

            $headings[] = [
                'level' => $level,
                'text' => $text,
                'id' => $id,
            ];
        }

        return $headings;
    }

    /**
     * Extract the ID from a heading element.
     *
     * Checks the heading itself first, then looks for a permalink anchor inside.
     */
    private function extractHeadingId(DOMElement $heading): string
    {
        $id = $heading->getAttribute('id');

        // If no ID on heading, check for permalink anchor inside
        if ($id === '' && $heading->firstChild instanceof DOMElement) {
            $firstChild = $heading->firstChild;

            if ($firstChild->nodeName === 'a' && str_contains($firstChild->getAttribute('class'), 'heading-permalink')) {
                $id = $firstChild->getAttribute('id');
            }
        }

        return $id;
    }

    /**
     * Extract text content from a heading, excluding permalink symbols.
     */
    private function extractTextContent(DOMElement $element): string
    {
        $text = '';

        foreach ($element->childNodes as $child) {
            // Skip anchor elements (permalink links)
            if ($child instanceof DOMElement && $child->nodeName === 'a') {
                // Check if it's a permalink anchor
                $class = $child->getAttribute('class');

                if (str_contains($class, 'heading-permalink')) {
                    continue;
                }
            }

            $text .= $child->textContent;
        }

        return trim($text);
    }
}

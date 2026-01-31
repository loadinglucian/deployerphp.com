<?php

declare(strict_types=1);

namespace App\Services;

final readonly class DocumentService
{
    public function __construct(
        private DocsPathService $docsPath,
        private MarkdownService $markdown,
        private HeadingExtractorService $headingExtractor,
    ) {}

    /**
     * Load and render a document.
     *
     * @return array{
     *     title: string,
     *     content: string,
     *     headings: array<int, array{level: int, text: string, id: string}>
     * }|null
     */
    public function load(string $page): ?array
    {
        $filePath = sprintf('%s/%s.md', $this->docsPath->path(), $page);

        if (! file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);

        if ($content === false) {
            return null;
        }

        $html = $this->markdown->toHtml($content);
        $headings = $this->headingExtractor->extract($html);
        $title = $this->extractTitle($html, $page);

        return [
            'title' => $title,
            'content' => $html,
            'headings' => $headings,
        ];
    }

    /**
     * Load and render the README from the parent docs directory.
     *
     * @return array{
     *     title: string,
     *     content: string,
     *     headings: array<int, array{level: int, text: string, id: string}>
     * }|null
     */
    public function loadReadme(): ?array
    {
        $filePath = dirname($this->docsPath->path()).'/README.md';

        if (! file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);

        if ($content === false) {
            return null;
        }

        $html = $this->markdown->toHtml($content);
        $headings = $this->headingExtractor->extract($html);
        $title = $this->extractTitle($html, 'README');

        return [
            'title' => $title,
            'content' => $html,
            'headings' => $headings,
        ];
    }

    /**
     * Extract title from HTML h1 or fallback to page name.
     */
    private function extractTitle(string $html, string $fallback): string
    {
        $h1 = $this->extractH1FromHtml($html);

        if ($h1 !== null && $h1 !== '') {
            return $h1;
        }

        // Convert slug to title case as fallback
        return str_replace('-', ' ', ucfirst($fallback));
    }

    /**
     * Extract h1 heading text directly from HTML.
     */
    private function extractH1FromHtml(string $html): ?string
    {
        if (trim($html) === '') {
            return null;
        }

        $dom = new \DOMDocument;
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR
        );

        $h1 = $dom->getElementsByTagName('h1')->item(0);

        if ($h1 === null) {
            return null;
        }

        return trim($h1->textContent);
    }
}

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
    public function load(string $section, string $page): ?array
    {
        $filePath = sprintf('%s/%s/%s.md', $this->docsPath->path(), $section, $page);

        if (! file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);

        if ($content === false) {
            return null;
        }

        $html = $this->markdown->toHtml($content);
        $headings = $this->headingExtractor->extract($html);

        // Extract title from first h1 heading
        $title = $this->extractTitle($headings, $page);

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
        $title = $this->extractTitle($headings, 'README');

        return [
            'title' => $title,
            'content' => $html,
            'headings' => $headings,
        ];
    }

    /**
     * Extract title from headings or fallback to page name.
     *
     * @param  array<int, array{level: int, text: string, id: string}>  $headings
     */
    private function extractTitle(array $headings, string $fallback): string
    {
        foreach ($headings as $heading) {
            if ($heading['level'] === 1) {
                return $heading['text'];
            }
        }

        // Convert slug to title case as fallback
        return str_replace('-', ' ', ucfirst($fallback));
    }
}

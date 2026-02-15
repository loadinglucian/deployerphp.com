<?php

declare(strict_types=1);

namespace App\Services;

final class TocParserService
{
    /**
     * @var array<int, array{
     *     name: string,
     *     anchor: string,
     *     links: array<int, array{title: string, path: string}>
     * }>|null
     */
    private ?array $cachedToc = null;

    public function __construct(
        private readonly DocsPathService $docsPath,
    ) {}

    /**
     * Parse the documentation.md file and return the TOC structure.
     *
     * @return array<int, array{
     *     name: string,
     *     anchor: string,
     *     links: array<int, array{title: string, path: string}>
     * }>
     */
    public function parse(): array
    {
        if ($this->cachedToc !== null) {
            return $this->cachedToc;
        }

        $path = $this->docsPath->path().'/documentation.md';

        if (! file_exists($path)) {
            $this->cachedToc = [];

            return $this->cachedToc;
        }

        $content = file_get_contents($path);

        if ($content === false) {
            $this->cachedToc = [];

            return $this->cachedToc;
        }

        $this->cachedToc = $this->parseContent($content);

        return $this->cachedToc;
    }

    /**
     * Parse TOC content into structured sections.
     *
     * @return array<int, array{
     *     name: string,
     *     anchor: string,
     *     links: array<int, array{title: string, path: string}>
     * }>
     */
    private function parseContent(string $content): array
    {
        $sections = [];
        $currentSection = null;
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            // Match section anchor: <a name="section-name"></a>
            if (preg_match('/<a\s+name="([^"]+)"><\/a>/', $line, $anchorMatch) === 1) {
                if ($currentSection !== null && $currentSection['links'] !== []) {
                    if ($currentSection['name'] === '') {
                        $currentSection['name'] = 'Documentation';
                    }

                    $sections[] = $currentSection;
                }

                $currentSection = [
                    'name' => '',
                    'anchor' => $anchorMatch[1],
                    'links' => [],
                ];

                continue;
            }

            // Match section header: ## Section Name
            if (
                $currentSection !== null
                && $currentSection['name'] === ''
                && preg_match('/^##\s+(.+)$/', $line, $headerMatch) === 1
            ) {
                $currentSection['name'] = trim($headerMatch[1]);

                continue;
            }

            // Match links: - [Title](path/to/file.md)
            if (preg_match('/^-\s+\[([^\]]+)\]\(([^)]+\.md)\)/', $line, $linkMatch) === 1) {
                // Create default section if none exists (flat structure)
                if ($currentSection === null) {
                    $currentSection = [
                        'name' => 'Documentation',
                        'anchor' => 'documentation',
                        'links' => [],
                    ];
                }

                $currentSection['links'][] = [
                    'title' => $linkMatch[1],
                    'path' => $this->normalizePath($linkMatch[2]),
                ];
            }
        }

        // Don't forget the last section
        if ($currentSection !== null && $currentSection['links'] !== []) {
            if ($currentSection['name'] === '') {
                $currentSection['name'] = 'Documentation';
            }

            $sections[] = $currentSection;
        }

        return $sections;
    }

    /**
     * Normalize a markdown file path to a flat URL path.
     *
     * Example: "getting-started/tldr.md" -> "tldr"
     */
    private function normalizePath(string $path): string
    {
        $path = explode('#', $path, 2)[0];
        $path = explode('?', $path, 2)[0];

        // Remove .md extension
        if (str_ends_with($path, '.md')) {
            $path = substr($path, 0, -3);
        }

        if (strtolower(basename($path)) === 'readme') {
            return '';
        }

        // Extract just the filename (flatten nested paths)
        return basename($path);
    }
}

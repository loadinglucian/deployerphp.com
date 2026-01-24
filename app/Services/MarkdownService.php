<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\HtmlString;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;

final readonly class MarkdownService
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $config = [
            'heading_permalink' => [
                'apply_id_to_heading' => true,
                'insert' => 'none',
                'id_prefix' => '',
                'fragment_prefix' => '',
                'min_heading_level' => 2,
                'max_heading_level' => 3,
            ],
        ];

        $environment = new Environment($config);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new HeadingPermalinkExtension);

        $this->converter = new MarkdownConverter($environment);
    }

    /**
     * Convert markdown to HTML with GitHub-style alert boxes, code block wrappers, and link transformation.
     */
    public function toHtml(string $markdown, ?string $currentSection = null): string
    {
        $html = $this->converter->convert($markdown)->getContent();
        $html = $this->stripContentBeforeH1($html);
        $html = $this->convertGitHubAlerts($html);
        $html = $this->wrapCodeBlocks($html);

        return $this->transformLinks($html, $currentSection);
    }

    /**
     * Remove any content that appears before the first H1 heading.
     */
    private function stripContentBeforeH1(string $html): string
    {
        $position = stripos($html, '<h1');

        if ($position === false) {
            return $html;
        }

        if ($position === 0) {
            return $html;
        }

        return substr($html, $position);
    }

    /**
     * Convert GitHub-style alerts to styled callout boxes.
     *
     * Transforms blockquotes like:
     *   > [!TIP]
     *   > Content here
     *
     * Into styled alert boxes with icons using Blade component.
     */
    private function convertGitHubAlerts(string $html): string
    {
        $alertTypes = ['TIP', 'NOTE', 'WARNING', 'IMPORTANT', 'CAUTION'];

        foreach ($alertTypes as $type) {
            // Pattern: <blockquote>\n<p>[!TYPE]\nContent...</p>\n</blockquote>
            $pattern = '/<blockquote>\s*<p>\[!'.$type.'\]\s*(.*?)<\/p>\s*<\/blockquote>/s';

            $html = preg_replace_callback(
                $pattern,
                fn (array $matches): string => view('components.docs.alert', [
                    'type' => strtolower($type),
                    'slot' => new HtmlString('<p>'.trim($matches[1]).'</p>'),
                ])->render(),
                $html
            ) ?? $html;
        }

        return $html;
    }

    /**
     * Wrap code blocks with the code-block component for copy functionality.
     */
    private function wrapCodeBlocks(string $html): string
    {
        return preg_replace_callback(
            '/<pre><code(?:\s+class="language-(\w+)")?>(.*?)<\/code><\/pre>/s',
            function (array $matches): string {
                $language = $matches[1] !== '' ? $matches[1] : null;
                $content = $matches[2];
                $langClass = $language === null ? '' : " class=\"language-{$language}\"";

                return view('components.docs.code-block', [
                    'language' => $language,
                    'slot' => new HtmlString(
                        "<pre><code{$langClass}>{$content}</code></pre>"
                    ),
                ])->render();
            },
            $html
        ) ?? $html;
    }

    /**
     * Transform relative links to GitHub blob URLs.
     *
     * Converts links like `section/file.md` or `/docs/section/page` to full
     * GitHub URLs pointing to the source repository.
     */
    private function transformLinks(string $html, ?string $currentSection): string
    {
        return preg_replace_callback(
            '/<a\s+href="([^"]+)"([^>]*)>/i',
            fn (array $matches): string => $this->transformLink($matches, $currentSection),
            $html
        ) ?? $html;
    }

    /**
     * Transform a single link based on its type.
     *
     * - Docs links → internal site routes (same tab)
     * - Non-docs links → GitHub URLs (new tab)
     *
     * @param  array<int, string>  $matches
     */
    private function transformLink(array $matches, ?string $currentSection): string
    {
        $href = $matches[1];
        $attributes = $matches[2];

        // Skip external links and anchor-only links
        if (preg_match('#^https?://#i', $href) === 1 || str_starts_with($href, '#')) {
            return $matches[0];
        }

        // Docs links (prefixed with /docs/ or docs/) → internal site route
        if ($this->isDocsLink($href)) {
            $internalUrl = $this->resolveInternalDocsUrl($href);

            return sprintf('<a href="%s"%s>', $internalUrl, $attributes);
        }

        // All other links → GitHub with target="_blank"
        $githubUrl = $this->resolveGitHubUrl($href, $currentSection);

        return sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer"%s>',
            $githubUrl,
            $attributes
        );
    }

    /**
     * Determine if a link points to a docs page by path prefix.
     */
    private function isDocsLink(string $href): bool
    {
        $pathWithoutFragment = explode('#', $href, 2)[0];

        return str_starts_with($pathWithoutFragment, '/docs/')
            || str_starts_with($pathWithoutFragment, 'docs/');
    }

    /**
     * Convert a docs link to an internal site route.
     */
    private function resolveInternalDocsUrl(string $href): string
    {
        // Preserve fragment
        $fragment = '';
        if (str_contains($href, '#')) {
            [$href, $fragment] = explode('#', $href, 2);
            $fragment = '#'.$fragment;
        }

        // Remove .md extension if present
        if (str_ends_with($href, '.md')) {
            $href = substr($href, 0, -3);
        }

        // Normalize: ensure leading slash if missing
        if (str_starts_with($href, 'docs/')) {
            $href = '/'.$href;
        }

        return "{$href}{$fragment}";
    }

    /**
     * Resolve a relative link to a full GitHub blob URL.
     */
    private function resolveGitHubUrl(string $href, ?string $currentSection): string
    {
        /** @var string $repo */
        $repo = config('docs.github.repo');
        /** @var string $branch */
        $branch = config('docs.github.branch');
        /** @var string $docsDir */
        $docsDir = config('docs.github.dir');

        $baseUrl = "https://github.com/{$repo}/blob/{$branch}";

        // Preserve fragment
        $fragment = '';
        if (str_contains($href, '#')) {
            [$href, $fragment] = explode('#', $href, 2);
            $fragment = '#'.$fragment;
        }

        // Handle root-relative paths (e.g., /CONTRIBUTING, /src/Command.php)
        // These point to the repo root, not the docs directory
        if (str_starts_with($href, '/')) {
            $path = substr($href, 1); // Remove leading slash

            return "{$baseUrl}/{$path}{$fragment}";
        }

        // Handle relative .md links (already have extension)
        if (str_ends_with($href, '.md')) {
            // If path doesn't start with section, prepend current section
            if ($currentSection !== null && ! str_contains($href, '/')) {
                return "{$baseUrl}/{$docsDir}/{$currentSection}/{$href}{$fragment}";
            }

            return "{$baseUrl}/{$docsDir}/{$href}{$fragment}";
        }

        // Other relative paths - add .md extension
        if ($currentSection !== null && ! str_contains($href, '/')) {
            return "{$baseUrl}/{$docsDir}/{$currentSection}/{$href}.md{$fragment}";
        }

        return "{$baseUrl}/{$docsDir}/{$href}.md{$fragment}";
    }
}

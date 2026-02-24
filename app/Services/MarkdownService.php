<?php

declare(strict_types=1);

namespace App\Services;

use App\Markdown\FencedCodeRenderer;
use Illuminate\Support\HtmlString;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
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

        // Register custom renderer to preserve info string modifiers (e.g., "nocopy")
        $environment->addRenderer(FencedCode::class, new FencedCodeRenderer, 10);

        $this->converter = new MarkdownConverter($environment);
    }

    /**
     * Convert markdown to HTML with GitHub-style alert boxes, code block wrappers, and link transformation.
     */
    public function toHtml(
        string $markdown,
        ?string $currentSection = null,
        ?string $sourceFilePath = null,
    ): string {
        $markdown = $this->stripMarkdownArtifacts($markdown);
        $html = $this->converter->convert($markdown)->getContent();
        $html = $this->stripContentBeforeH1($html);
        $html = $this->convertGitHubAlerts($html);
        $html = $this->wrapCodeBlocks($html);

        return $this->transformLinks($html, $currentSection, $sourceFilePath);
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
     * Remove embedded TOC sections and legacy anchor points.
     *
     * Strips:
     * - TOC blocks: <!-- toc --> ... <!-- /toc -->
     * - Empty anchors: <a name="..."></a>
     *
     * These are redundant since the site generates its own navigation.
     */
    private function stripMarkdownArtifacts(string $markdown): string
    {
        // Strip TOC comment blocks
        $markdown = preg_replace(
            '/<!-- toc -->.*?<!-- \/toc -->/s',
            '',
            $markdown
        ) ?? $markdown;

        // Strip empty anchor points (legacy heading anchors)
        return preg_replace(
            '/<a\s+name="[^"]*">\s*<\/a>\s*/i',
            '',
            $markdown
        ) ?? $markdown;
    }

    /**
     * Convert GitHub-style alerts to styled callout boxes.
     *
     * Transforms blockquotes like:
     *   > [!NOTE]
     *   > Content here
     *
     * Into styled alert boxes with icons using Blade component.
     */
    private function convertGitHubAlerts(string $html): string
    {
        // Pattern:
        // <blockquote>
        //   <p>[!TYPE] ...optional first paragraph content...</p>
        //   ...optional additional block elements (e.g. <p>, <ul>, <pre>)...
        // </blockquote>
        return preg_replace_callback(
            '/<blockquote>\s*<p>\s*\[!(NOTE|IMPORTANT)\]\s*(.*?)<\/p>(.*?)<\/blockquote>/is',
            function (array $matches): string {
                $type = strtolower(trim($matches[1]));
                $firstParagraph = trim($matches[2]);
                $remainingBlocks = trim($matches[3]);

                $content = '';
                if ($firstParagraph !== '') {
                    $content = '<p>'.$firstParagraph.'</p>';
                }

                if ($remainingBlocks !== '') {
                    $content .= $content === '' ? $remainingBlocks : "\n{$remainingBlocks}";
                }

                if ($content === '') {
                    return $matches[0];
                }

                return view('components.docs.alert', [
                    'type' => $type,
                    'slot' => new HtmlString($content),
                ])->render();
            },
            $html
        ) ?? $html;
    }

    /**
     * Wrap code blocks with the code-block component for copy functionality.
     *
     * Captures language class and data-modifiers attribute from the custom renderer.
     */
    private function wrapCodeBlocks(string $html): string
    {
        return preg_replace_callback(
            '/<pre(?:\s+data-modifiers="([^"]*)")?><code(?:\s+class="language-(\w+)")?>(.*?)<\/code><\/pre>/s',
            function (array $matches): string {
                $modifiers = $matches[1] !== '' ? $matches[1] : null;
                $language = $matches[2] !== '' ? $matches[2] : null;
                $content = $matches[3];
                $langClass = $language === null ? '' : " class=\"language-{$language}\"";

                return view('components.docs.code-block', [
                    'language' => $language,
                    'modifiers' => $modifiers,
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
    private function transformLinks(string $html, ?string $currentSection, ?string $sourceFilePath): string
    {
        return preg_replace_callback(
            '/<a\s+href="([^"]+)"([^>]*)>/i',
            fn (array $matches): string => $this->transformLink($matches, $currentSection, $sourceFilePath),
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
    private function transformLink(array $matches, ?string $currentSection, ?string $sourceFilePath): string
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

        $relativeDocsUrl = $this->resolveRelativeDocsUrl($href, $sourceFilePath);

        if ($relativeDocsUrl !== null) {
            return sprintf('<a href="%s"%s>', $relativeDocsUrl, $attributes);
        }

        // All other links → GitHub with target="_blank"
        $githubUrl = $this->resolveGitHubUrl($href, $currentSection);

        return sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer"%s>',
            $githubUrl,
            $attributes
        );
    }

    private function resolveRelativeDocsUrl(string $href, ?string $sourceFilePath): ?string
    {
        if ($sourceFilePath === null || str_starts_with($href, '/')) {
            return null;
        }

        [$path, $fragment] = $this->splitFragment($href);

        if ($path === '' || preg_match('/^[a-z][a-z0-9+.-]*:/i', $path) === 1) {
            return null;
        }

        $docsDirectory = config('docs.path');

        if (! is_string($docsDirectory) || $docsDirectory === '') {
            return null;
        }

        $absoluteDocsDirectory = str_starts_with($docsDirectory, '/')
            ? $docsDirectory
            : base_path($docsDirectory);

        $resolvedDocsDirectory = realpath($absoluteDocsDirectory);
        $resolvedSourcePath = realpath($sourceFilePath);

        if ($resolvedDocsDirectory === false || $resolvedSourcePath === false) {
            return null;
        }

        $sourceDirectory = dirname($resolvedSourcePath);
        $candidatePath = $path;

        if (! str_ends_with($candidatePath, '.md')) {
            $candidatePath .= '.md';
        }

        $resolvedTargetPath = realpath($sourceDirectory.'/'.$candidatePath);

        if ($resolvedTargetPath === false || ! str_ends_with($resolvedTargetPath, '.md')) {
            return null;
        }

        $resolvedDocsParentReadme = realpath(dirname($resolvedDocsDirectory).'/README.md');

        if ($resolvedDocsParentReadme !== false && $resolvedTargetPath === $resolvedDocsParentReadme) {
            return '/'.$fragment;
        }

        $docsPrefix = rtrim($resolvedDocsDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        if (! str_starts_with($resolvedTargetPath, $docsPrefix)) {
            return null;
        }

        $relativeTargetPath = substr($resolvedTargetPath, strlen($docsPrefix));
        $page = preg_replace('/\.md$/', '', $relativeTargetPath);

        if ($page === null || $page === '' || preg_match('/^[a-z0-9-]+$/', $page) !== 1) {
            return null;
        }

        return "/docs/{$page}{$fragment}";
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitFragment(string $href): array
    {
        if (! str_contains($href, '#')) {
            return [$href, ''];
        }

        [$path, $fragment] = explode('#', $href, 2);

        return [$path, '#'.$fragment];
    }

    /**
     * Determine if a link points to a docs page by path prefix.
     */
    private function isDocsLink(string $href): bool
    {
        $pathWithoutFragment = explode('#', $href, 2)[0];

        return $pathWithoutFragment === '/docs'
            || $pathWithoutFragment === 'docs'
            || str_starts_with($pathWithoutFragment, '/docs/')
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

        if ($href === '/docs' || $href === 'docs' || $href === '/docs/') {
            return "/{$fragment}";
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

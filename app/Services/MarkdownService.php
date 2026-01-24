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
     * Convert markdown to HTML with GitHub-style alert boxes and code block wrappers.
     */
    public function toHtml(string $markdown): string
    {
        $html = $this->converter->convert($markdown)->getContent();
        $html = $this->convertGitHubAlerts($html);

        return $this->wrapCodeBlocks($html);
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
}

<?php

declare(strict_types=1);

namespace App\Services;

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
        $environment = new Environment([
            'heading_permalink' => [
                'html_class' => 'heading-permalink',
                'id_prefix' => '',
                'fragment_prefix' => '',
                'insert' => 'before',
                'min_heading_level' => 1,
                'max_heading_level' => 6,
                'title' => 'Permalink',
                'symbol' => '#',
                'aria_hidden' => true,
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new HeadingPermalinkExtension);

        $this->converter = new MarkdownConverter($environment);
    }

    /**
     * Convert markdown to HTML with GitHub-style alert boxes.
     */
    public function toHtml(string $markdown): string
    {
        $html = $this->converter->convert($markdown)->getContent();

        return $this->convertGitHubAlerts($html);
    }

    /**
     * Convert GitHub-style alerts to styled callout boxes.
     *
     * Transforms blockquotes like:
     *   > [!TIP]
     *   > Content here
     *
     * Into styled alert boxes with icons.
     */
    private function convertGitHubAlerts(string $html): string
    {
        $alertTypes = [
            'TIP' => [
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5"><path d="M8 1.5A5.5 5.5 0 0 0 2.5 7c0 1.58.67 3 1.74 4.01L4 15.5h8l-.24-4.49A5.5 5.5 0 0 0 8 1.5zM5.5 14.5v-1h5v1h-5z"/></svg>',
                'class' => 'alert-tip',
            ],
            'NOTE' => [
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5"><path fill-rule="evenodd" d="M8 1.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13zM7.25 8.5v-3h1.5v3h-1.5zm0 2.25a.75.75 0 1 1 1.5 0 .75.75 0 0 1-1.5 0z" clip-rule="evenodd"/></svg>',
                'class' => 'alert-note',
            ],
            'WARNING' => [
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5"><path fill-rule="evenodd" d="M8.893 1.5c-.183-.31-.52-.5-.887-.5s-.704.19-.887.5L.387 12.996c-.182.31-.182.69 0 1 .183.31.52.5.887.5h13.452c.367 0 .704-.19.887-.5.183-.31.183-.69 0-1L8.893 1.5zM7.25 5h1.5v4h-1.5V5zm.75 6.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5z" clip-rule="evenodd"/></svg>',
                'class' => 'alert-warning',
            ],
            'IMPORTANT' => [
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5"><path fill-rule="evenodd" d="M8 1.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13zM7.25 4.75a.75.75 0 1 1 1.5 0 .75.75 0 0 1-1.5 0zm1.5 2.5v4h-1.5v-4h1.5z" clip-rule="evenodd"/></svg>',
                'class' => 'alert-important',
            ],
            'CAUTION' => [
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5"><path fill-rule="evenodd" d="M8 1.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13zM6.22 6.22a.75.75 0 0 1 1.06 0L8 6.94l.72-.72a.75.75 0 1 1 1.06 1.06l-.72.72.72.72a.75.75 0 1 1-1.06 1.06L8 9.06l-.72.72a.75.75 0 1 1-1.06-1.06l.72-.72-.72-.72a.75.75 0 0 1 0-1.06z" clip-rule="evenodd"/></svg>',
                'class' => 'alert-caution',
            ],
        ];

        foreach ($alertTypes as $type => $config) {
            // Pattern: <blockquote>\n<p>[!TYPE]\nContent...</p>\n</blockquote>
            $pattern = '/<blockquote>\s*<p>\[!'.$type.'\]\s*(.*?)<\/p>\s*<\/blockquote>/s';

            $html = preg_replace_callback(
                $pattern,
                fn (array $matches): string => sprintf(
                    '<div class="alert %s"><span class="alert-icon">%s</span><div class="alert-content"><p>%s</p></div></div>',
                    $config['class'],
                    $config['icon'],
                    trim($matches[1])
                ),
                $html
            ) ?? $html;
        }

        return $html;
    }
}

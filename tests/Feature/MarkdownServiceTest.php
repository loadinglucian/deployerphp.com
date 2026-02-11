<?php

declare(strict_types=1);

use App\Services\MarkdownService;

it('renders important github alerts with additional block content', function (): void {
    $markdown = <<<'MARKDOWN'
> [!IMPORTANT]
> First important callout.
>
> - First item
> - Second item
>
> Another paragraph.
MARKDOWN;

    $html = app(MarkdownService::class)->toHtml($markdown);

    expect(substr_count($html, 'border-l-amber-400'))->toBe(1)
        ->and(substr_count($html, '<svg'))->toBe(1)
        ->and($html)->toContain('<ul>')
        ->toContain('<li>First item</li>')
        ->toContain('<li>Second item</li>');
});

it('renders multiple important github alerts on the same page', function (): void {
    $markdown = <<<'MARKDOWN'
> [!IMPORTANT]
> First important callout.

> [!IMPORTANT]
> Second important callout.
MARKDOWN;

    $html = app(MarkdownService::class)->toHtml($markdown);

    expect(substr_count($html, 'border-l-amber-400'))->toBe(2)
        ->and(substr_count($html, '<svg'))->toBe(2);
});

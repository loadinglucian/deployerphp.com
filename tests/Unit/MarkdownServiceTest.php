<?php

declare(strict_types=1);

use App\Services\MarkdownService;

beforeEach(function (): void {
    config()->set('docs.github.repo', 'acme/docs-repo');
    config()->set('docs.github.branch', 'main');
    config()->set('docs.github.dir', 'docs');
});

it('strips markdown artifacts and content before the first h1', function (): void {
    $markdown = <<<'MARKDOWN'
Intro paragraph that should be removed.

<!-- toc -->
- fake toc
<!-- /toc -->

<a name="legacy-anchor"></a>

# Real Title

Body
MARKDOWN;

    $html = app(MarkdownService::class)->toHtml($markdown);

    expect($html)
        ->toContain('<h1>Real Title</h1>')
        ->toContain('<p>Body</p>')
        ->not->toContain('Intro paragraph that should be removed.')
        ->not->toContain('legacy-anchor');
});

it('converts docs links to internal routes and preserves fragments', function (): void {
    $markdown = <<<'MARKDOWN'
# Links

[Docs Home](/docs)
[Install](docs/installation#requirements)
MARKDOWN;

    $html = app(MarkdownService::class)->toHtml($markdown);

    expect($html)
        ->toContain('<a href="/">Docs Home</a>')
        ->toContain('<a href="/docs/installation#requirements">Install</a>');
});

it('converts relative links to github blob urls', function (): void {
    $markdown = <<<'MARKDOWN'
# Links

[Relative Section](setup)
[Relative Markdown](guides/setup.md#cli)
[Root Path](/CONTRIBUTING#policy)
[Anchor](#local)
[External](https://example.com)
MARKDOWN;

    $html = app(MarkdownService::class)->toHtml($markdown, 'deploy');

    expect($html)
        ->toContain('href="https://github.com/acme/docs-repo/blob/main/docs/deploy/setup.md" target="_blank" rel="noopener noreferrer"')
        ->toContain('href="https://github.com/acme/docs-repo/blob/main/docs/guides/setup.md#cli" target="_blank" rel="noopener noreferrer"')
        ->toContain('href="https://github.com/acme/docs-repo/blob/main/CONTRIBUTING#policy" target="_blank" rel="noopener noreferrer"')
        ->toContain('<a href="#local">Anchor</a>')
        ->toContain('<a href="https://example.com">External</a>');
});

it('wraps code blocks and respects nocopy modifiers', function (): void {
    $markdown = <<<'MARKDOWN'
# Code

```bash nocopy
php artisan test
```

```php
<?php echo 'ok';
```
MARKDOWN;

    $html = app(MarkdownService::class)->toHtml($markdown);

    expect(substr_count($html, 'aria-label="Copy code to clipboard"'))->toBe(1)
        ->and($html)->toContain('language-bash')
        ->toContain('language-php');
});

it('renders important alerts with block content', function (): void {
    $markdown = <<<'MARKDOWN'
> [!IMPORTANT]
> First important callout.
>
> - First item
> - Second item
MARKDOWN;

    $html = app(MarkdownService::class)->toHtml($markdown);

    expect(substr_count($html, 'dark:border-l-amber-400'))->toBe(1)
        ->and($html)
        ->toContain('<li>First item</li>')
        ->toContain('<li>Second item</li>');
});

it('renders note alerts with block content', function (): void {
    $markdown = <<<'MARKDOWN'
> [!NOTE]
> First note callout.
>
> - First item
> - Second item
MARKDOWN;

    $html = app(MarkdownService::class)->toHtml($markdown);

    expect(substr_count($html, 'dark:border-l-cyan-400'))->toBe(1)
        ->and($html)
        ->toContain('<li>First item</li>')
        ->toContain('<li>Second item</li>');
});

it('keeps empty github alert markers unchanged when there is no content', function (): void {
    $markdown = <<<'MARKDOWN'
> [!IMPORTANT]
MARKDOWN;

    $html = app(MarkdownService::class)->toHtml($markdown);

    expect($html)->toContain('[!IMPORTANT]');
});

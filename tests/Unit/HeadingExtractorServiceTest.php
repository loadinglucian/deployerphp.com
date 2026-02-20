<?php

declare(strict_types=1);

use App\Services\HeadingExtractorService;

it('returns an empty array for blank html', function (): void {
    $headings = app(HeadingExtractorService::class)->extract('  ');

    expect($headings)->toBe([]);
});

it('extracts h2 and h3 headings and ignores h1', function (): void {
    $html = <<<'HTML'
<h1 id="top">Top</h1>
<h2 id="requirements">Requirements</h2>
<h3 id="steps">Steps</h3>
HTML;

    $headings = app(HeadingExtractorService::class)->extract($html);

    expect($headings)->toBe([
        ['level' => 2, 'text' => 'Requirements', 'id' => 'requirements'],
        ['level' => 3, 'text' => 'Steps', 'id' => 'steps'],
    ]);
});

it('extracts heading ids from permalink anchors when no id exists on heading', function (): void {
    $html = <<<'HTML'
<h2><a id="linked-heading" class="heading-permalink" href="#linked-heading">#</a>Heading Title</h2>
HTML;

    $headings = app(HeadingExtractorService::class)->extract($html);

    expect($headings)->toBe([
        ['level' => 2, 'text' => 'Heading Title', 'id' => 'linked-heading'],
    ]);
});

it('skips headings that do not contain visible text', function (): void {
    $html = <<<'HTML'
<h2><a id="empty" class="heading-permalink" href="#empty">#</a></h2>
HTML;

    $headings = app(HeadingExtractorService::class)->extract($html);

    expect($headings)->toBe([]);
});

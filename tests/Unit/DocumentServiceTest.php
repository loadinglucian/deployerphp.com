<?php

declare(strict_types=1);

use App\Services\DocsOutputCacheService;
use App\Services\DocsPathService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\File;
use Tests\Concerns\UsesDocsFixtures;

uses(UsesDocsFixtures::class);

beforeEach(function (): void {
    $this->configureDocsFixtures();
});

it('loads and renders fixture documentation pages', function (): void {
    $document = app(DocumentService::class)->load('installation');

    expect($document)->not->toBeNull();
    expect($document)
        ->toHaveKey('title', 'Installation')
        ->and($document['content'])->toContain('<h2 id="requirements">Requirements</h2>')
        ->and($document['headings'])->toBe([
            ['level' => 2, 'text' => 'Requirements', 'id' => 'requirements'],
            ['level' => 2, 'text' => 'Steps', 'id' => 'steps'],
        ]);
});

it('loads the fixture readme from the docs parent directory', function (): void {
    $document = app(DocumentService::class)->loadReadme();

    expect($document)->not->toBeNull();
    expect($document)
        ->toHaveKey('title', 'Fixture Documentation Home')
        ->and($document['content'])->toContain('fixture-based docs home');
});

it('returns null when requested docs page is missing', function (): void {
    expect(app(DocumentService::class)->load('missing-page'))->toBeNull();
});

it('falls back to title-cased slug when no h1 is present', function (): void {
    $docsPath = storage_path('framework/testing/docs-no-title-'.uniqid('', true));

    File::ensureDirectoryExists($docsPath);
    File::put("{$docsPath}/no-title.md", "Paragraph only\n\n## Section");

    config()->set('docs.path', $docsPath);
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(DocumentService::class);

    $document = app(DocumentService::class)->load('no-title');

    expect($document)->not->toBeNull();
    expect($document['title'])->toBe('No title');

    File::deleteDirectory($docsPath);
});

it('uses cached document payloads keyed by file fingerprint', function (): void {
    $service = app(DocumentService::class);
    $cache = app(DocsOutputCacheService::class);

    $path = $this->docsFixtureRoot().'/docs/installation.md';
    $modifiedAt = filemtime($path);
    $size = filesize($path);
    $fingerprint = sprintf('%d:%d', $modifiedAt === false ? 0 : $modifiedAt, $size === false ? 0 : $size);

    $cache->put(
        $cache->key('document', ['page:installation', $fingerprint]),
        [
            'title' => 'From Cache',
            'content' => '<h1>From Cache</h1><p>Cached.</p>',
            'headings' => [],
        ],
    );

    $document = $service->load('installation');

    expect($document)->toBe([
        'title' => 'From Cache',
        'content' => '<h1>From Cache</h1><p>Cached.</p>',
        'headings' => [],
    ]);
});

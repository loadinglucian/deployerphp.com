<?php

declare(strict_types=1);

use App\Services\DocsOutputCacheService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\File;

it('reuses cached document payloads by file fingerprint', function (): void {
    $docsPath = storage_path('framework/testing/docs-'.uniqid('', true));
    $cachePath = storage_path('framework/cache/docs-output-tests/'.uniqid('', true));

    File::ensureDirectoryExists($docsPath);
    File::put("{$docsPath}/cached-page.md", "# Cached Page\n\nBody content");

    config()->set('docs.path', $docsPath);
    config()->set('docs.cache.path', $cachePath);

    app()->forgetInstance(DocsOutputCacheService::class);
    app()->forgetInstance(DocumentService::class);

    $docsOutputCache = app(DocsOutputCacheService::class);
    $documentService = app(DocumentService::class);

    $firstLoad = $documentService->load('cached-page');

    expect($firstLoad)->not->toBeNull();

    $modifiedAt = filemtime("{$docsPath}/cached-page.md");
    $size = filesize("{$docsPath}/cached-page.md");
    $fingerprint = sprintf(
        '%d:%d',
        $modifiedAt === false ? 0 : $modifiedAt,
        $size === false ? 0 : $size,
    );

    $docsOutputCache->put(
        $docsOutputCache->key('document', ['page:cached-page', $fingerprint]),
        [
            'title' => 'From Cache',
            'content' => '<h1>From Cache</h1><p>Cached document payload.</p>',
            'headings' => [],
        ],
    );

    $secondLoad = $documentService->load('cached-page');

    expect($secondLoad)->toMatchArray([
        'title' => 'From Cache',
        'content' => '<h1>From Cache</h1><p>Cached document payload.</p>',
        'headings' => [],
    ]);

    File::deleteDirectory($docsPath);
    File::deleteDirectory($cachePath);
});

<?php

declare(strict_types=1);

use App\Services\DocsOutputCacheService;
use Illuminate\Support\Facades\File;

it('clears docs output when optimize clear runs', function (): void {
    $cachePath = storage_path('framework/cache/docs-output-tests/'.uniqid('', true));

    config()->set('docs.cache.path', $cachePath);

    app()->forgetInstance(DocsOutputCacheService::class);

    $docsOutput = app(DocsOutputCacheService::class);
    $docsOutput->put(
        $docsOutput->key('docs-viewer', ['home']),
        ['title' => 'Cached'],
    );

    expect(File::allFiles($cachePath))->not->toBeEmpty();

    $this->artisan('optimize:clear')->assertSuccessful();

    expect(File::isDirectory($cachePath))->toBeTrue();
    expect(File::allFiles($cachePath))->toBeEmpty();
});

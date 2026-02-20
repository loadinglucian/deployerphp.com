<?php

declare(strict_types=1);

use App\Services\DocsOutputCacheService;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    config()->set('docs.cache.enabled', true);
    config()->set('docs.cache.version', 'v1');
    config()->set('docs.cache.path', storage_path('framework/cache/docs-output-tests/'.uniqid('', true)));

    app()->forgetInstance(DocsOutputCacheService::class);

    app(DocsOutputCacheService::class)->clear();
});

it('builds stable cache keys from normalized segments', function (): void {
    $service = app(DocsOutputCacheService::class);

    $key = $service->key('docs-viewer', [true, false, null, ['page' => 'home']]);

    expect($key)
        ->toContain('v1:docs-viewer:true:false:null')
        ->toContain('{"page":"home"}');
});

it('persists and retrieves cache payloads', function (): void {
    $service = app(DocsOutputCacheService::class);
    $key = $service->key('document', ['page:installation']);

    $service->put($key, ['title' => 'Installation']);

    expect($service->get($key))->toBe(['title' => 'Installation']);
});

it('returns null for stale versioned payloads', function (): void {
    $service = app(DocsOutputCacheService::class);
    $key = $service->key('document', ['page:installation']);

    $service->put($key, ['title' => 'Installation']);

    config()->set('docs.cache.version', 'v2');
    app()->forgetInstance(DocsOutputCacheService::class);

    expect(app(DocsOutputCacheService::class)->get($key))->toBeNull();
});

it('returns null for key mismatches and invalid payloads', function (): void {
    $service = app(DocsOutputCacheService::class);
    $key = $service->key('document', ['page:installation']);

    $hash = sha1($key);
    $path = config('docs.cache.path').'/'.substr($hash, 0, 2)."/{$hash}.json";

    File::ensureDirectoryExists(dirname($path));
    File::put($path, '{bad json');

    expect($service->get($key))->toBeNull();

    File::put($path, json_encode([
        'version' => 'v1',
        'key' => 'different-key',
        'value' => ['title' => 'Wrong'],
    ], JSON_UNESCAPED_SLASHES));

    expect($service->get($key))->toBeNull();
});

it('bypasses cache reads and writes when disabled', function (): void {
    config()->set('docs.cache.enabled', false);
    app()->forgetInstance(DocsOutputCacheService::class);

    $service = app(DocsOutputCacheService::class);
    $key = $service->key('document', ['page:installation']);

    $service->put($key, ['title' => 'Installation']);

    expect($service->get($key))->toBeNull();

    $calls = 0;

    $first = $service->remember($key, function () use (&$calls): array {
        $calls++;

        return ['title' => 'A'];
    });

    $second = $service->remember($key, function () use (&$calls): array {
        $calls++;

        return ['title' => 'B'];
    });

    expect($first)->toBe(['title' => 'A'])
        ->and($second)->toBe(['title' => 'B'])
        ->and($calls)->toBe(2);
});

it('memoizes resolver values with remember when enabled', function (): void {
    $service = app(DocsOutputCacheService::class);
    $key = $service->key('document', ['page:installation']);
    $calls = 0;

    $first = $service->remember($key, function () use (&$calls): array {
        $calls++;

        return ['title' => 'Installation'];
    });

    $second = $service->remember($key, function () use (&$calls): array {
        $calls++;

        return ['title' => 'Changed'];
    });

    expect($first)->toBe(['title' => 'Installation'])
        ->and($second)->toBe(['title' => 'Installation'])
        ->and($calls)->toBe(1);
});

it('clears cache files while keeping the cache directory present', function (): void {
    $service = app(DocsOutputCacheService::class);
    $key = $service->key('document', ['page:installation']);

    $service->put($key, ['title' => 'Installation']);

    $cachePath = config('docs.cache.path');

    expect(File::allFiles($cachePath))->not->toBeEmpty();

    $service->clear();

    expect(File::isDirectory($cachePath))->toBeTrue()
        ->and(File::allFiles($cachePath))->toBeEmpty();
});

it('falls back to default cache path when config path is invalid', function (): void {
    config()->set('docs.cache.path', null);
    app()->forgetInstance(DocsOutputCacheService::class);

    $service = app(DocsOutputCacheService::class);

    expect($service->path())->toBe(storage_path('framework/cache/docs-output'));
});

<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Services\CommandIndexService;
use App\Services\DocsOutputCacheService;
use App\Services\DocsPathService;
use App\Services\DocumentService;
use App\Services\TocParserService;

trait UsesDocsFixtures
{
    protected function configureDocsFixtures(?string $cacheSuffix = null): void
    {
        $root = $this->docsFixtureRoot();
        $cachePath = storage_path('framework/cache/docs-output-tests/'.($cacheSuffix ?? uniqid('', true)));

        config()->set('docs.path', "{$root}/docs");
        config()->set('docs.cache.path', $cachePath);
        config()->set('docs.cache.enabled', true);
        config()->set('docs.cache.version', 'v1');

        app()->forgetInstance(DocsPathService::class);
        app()->forgetInstance(DocsOutputCacheService::class);
        app()->forgetInstance(TocParserService::class);
        app()->forgetInstance(DocumentService::class);
        app()->forgetInstance(CommandIndexService::class);

        app(DocsOutputCacheService::class)->clear();
    }

    protected function docsFixtureRoot(): string
    {
        return base_path('tests/Fixtures/docs');
    }
}

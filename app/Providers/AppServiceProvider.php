<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\DocsOutputCacheService;
use App\Services\DocsPathService;
use App\Services\DocumentService;
use App\Services\HeadingExtractorService;
use App\Services\MarkdownService;
use App\Services\TocParserService;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(DocsPathService::class);
        $this->app->singleton(DocsOutputCacheService::class);
        $this->app->singleton(MarkdownService::class);
        $this->app->singleton(TocParserService::class);
        $this->app->singleton(HeadingExtractorService::class);
        $this->app->singleton(DocumentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->optimizes(clear: 'docs:clear', key: 'docs');
    }
}

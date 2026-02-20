<?php

declare(strict_types=1);

use App\Livewire\DocsViewer;
use App\Services\DocsOutputCacheService;
use App\Services\DocsPathService;
use App\Services\DocumentService;
use App\Services\TocParserService;
use Livewire\Livewire;
use Tests\Concerns\UsesDocsFixtures;

uses(UsesDocsFixtures::class);

beforeEach(function (): void {
    $this->configureDocsFixtures();
});

it('hydrates docs viewer state for the docs home', function (): void {
    Livewire::test(DocsViewer::class)
        ->assertSet('page', '')
        ->assertSet('title', 'Fixture Documentation Home')
        ->assertSet('toc.0.name', 'Guides');
});

it('hydrates docs viewer state for a docs page', function (): void {
    Livewire::test(DocsViewer::class, ['page' => 'installation'])
        ->assertSet('page', 'installation')
        ->assertSet('title', 'Installation')
        ->assertSet('headings.0.text', 'Requirements');
});

it('uses cached viewer payload when available', function (): void {
    $cache = app(DocsOutputCacheService::class);

    $cache->put(
        $cache->key('docs-viewer', ['home']),
        [
            'page' => '',
            'title' => 'Cached Home',
            'content' => '<h1>Cached Home</h1>',
            'headings' => [],
            'toc' => [
                [
                    'name' => 'Cached',
                    'anchor' => 'cached',
                    'links' => [
                        ['title' => 'Cached Link', 'path' => 'cached-link'],
                    ],
                ],
            ],
        ],
    );

    config()->set('docs.path', storage_path('framework/testing/missing-docs-'.uniqid('', true)));
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(TocParserService::class);
    app()->forgetInstance(DocumentService::class);

    Livewire::test(DocsViewer::class)
        ->assertSet('title', 'Cached Home')
        ->assertSet('toc.0.name', 'Cached');
});

it('redirects to home when a docs page does not exist', function (): void {
    Livewire::test(DocsViewer::class, ['page' => 'missing-page'])
        ->assertRedirect(route('home'));
});

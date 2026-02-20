<?php

declare(strict_types=1);

use App\Services\CommandIndexService;
use App\Services\DocsOutputCacheService;
use App\Services\DocsPathService;
use App\Services\TocParserService;

beforeEach(function (): void {
    config()->set('docs.cache.path', storage_path('framework/cache/docs-output-tests/'.uniqid('', true)));

    app()->forgetInstance(DocsOutputCacheService::class);
    app(DocsOutputCacheService::class)->clear();
});

it('renders the command index with discovered commands', function (): void {
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(CommandIndexService::class);

    $response = $this->get(route('command-index'));

    $response->assertOk()
        ->assertSee('Command Index')
        ->assertSeeText('Server & Site Operations')
        ->assertSeeText('Scheduling & Process Control')
        ->assertSeeText('Web Runtime Services')
        ->assertSeeText('Data Services')
        ->assertSeeText('Cloud Providers')
        ->assertSee('server:add')
        ->assertSee('site:deploy');
});

it('does not render docs reference links for commands', function (): void {
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(CommandIndexService::class);

    $response = $this->get(route('command-index'));

    $response->assertOk()
        ->assertSeeText('site:delete')
        ->assertDontSee(route('docs.show', ['page' => 'managing-sites']).'#deleting-a-site', false);
});

it('renders the empty-state message when no commands are available', function (): void {
    $docsOutputCache = app(DocsOutputCacheService::class);

    $docsOutputCache->put(
        $docsOutputCache->key('command-index'),
        [
            'toc' => [],
            'sections' => [],
            'groups' => [],
            'commandCount' => 0,
            'aliasCount' => 0,
        ],
    );

    $response = $this->get(route('command-index'));

    $response->assertOk()
        ->assertSeeText('No commands were discovered for the configured docs path.');
});

it('renders the docs-style single sidebar layout', function (): void {
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(TocParserService::class);
    app()->forgetInstance(CommandIndexService::class);

    $response = $this->get(route('command-index'));

    $response->assertOk()
        ->assertSee('href="'.route('home').'"', false)
        ->assertSeeText('Guides')
        ->assertSee('lg:grid-cols-[280px_1fr]', false)
        ->assertDontSee('lg:grid-cols-[280px_1fr_280px]', false);
});

it('uses cached command index payload when available', function (): void {
    $docsOutputCache = app(DocsOutputCacheService::class);
    $unresolvableDocsPath = storage_path('framework/testing/non-existent-docs-'.uniqid('', true));

    config()->set('docs.path', $unresolvableDocsPath);
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(TocParserService::class);
    app()->forgetInstance(CommandIndexService::class);

    $docsOutputCache->put(
        $docsOutputCache->key('command-index'),
        [
            'toc' => [
                [
                    'name' => 'Cached Nav',
                    'anchor' => 'cached-nav',
                    'links' => [
                        ['title' => 'Cached Command Index', 'path' => 'command-index'],
                    ],
                ],
            ],
            'sections' => [
                [
                    'name' => 'Cached Section',
                    'count' => 1,
                    'namespaceCount' => 1,
                    'groups' => [
                        [
                            'name' => 'Cached',
                            'count' => 1,
                            'commands' => [
                                [
                                    'primary' => 'cached:run',
                                    'aliases' => [],
                                    'description' => 'Cached command description',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'groups' => [
                [
                    'name' => 'Cached',
                    'count' => 1,
                    'commands' => [
                        [
                            'primary' => 'cached:run',
                            'aliases' => [],
                            'description' => 'Cached command description',
                        ],
                    ],
                ],
            ],
            'commandCount' => 1,
            'aliasCount' => 0,
        ],
    );

    $response = $this->get(route('command-index'));

    $response->assertOk()
        ->assertSeeText('Cached Section')
        ->assertSeeText('cached:run')
        ->assertSeeText('Cached Nav');
});

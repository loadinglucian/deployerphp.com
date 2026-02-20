<?php

declare(strict_types=1);

use App\Services\CommandIndexService;
use App\Services\DocsOutputCacheService;
use App\Services\DocsPathService;
use App\Services\TocParserService;
use Tests\Concerns\UsesDocsFixtures;

uses(UsesDocsFixtures::class);

beforeEach(function (): void {
    $this->configureDocsFixtures();
});

it('renders the command index using fixture commands', function (): void {
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(CommandIndexService::class);

    $response = $this->get(route('command-index'));

    $response->assertOk()
        ->assertSeeText('Command Index')
        ->assertSeeText('server:add')
        ->assertSeeText('aws:region')
        ->assertSeeText('custom-task');
});

it('uses cached command index payload when docs are unavailable', function (): void {
    $docsOutputCache = app(DocsOutputCacheService::class);

    $docsOutputCache->put(
        $docsOutputCache->key('command-index'),
        [
            'toc' => [
                [
                    'name' => 'Cached',
                    'anchor' => 'cached',
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
                            'name' => 'Cached Group',
                            'count' => 1,
                            'commands' => [
                                [
                                    'primary' => 'cached:run',
                                    'aliases' => [],
                                    'description' => 'Cached command',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'groups' => [
                [
                    'name' => 'Cached Group',
                    'count' => 1,
                    'commands' => [
                        [
                            'primary' => 'cached:run',
                            'aliases' => [],
                            'description' => 'Cached command',
                        ],
                    ],
                ],
            ],
            'commandCount' => 1,
            'aliasCount' => 0,
        ],
    );

    config()->set('docs.path', storage_path('framework/testing/missing-docs-'.uniqid('', true)));
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(TocParserService::class);
    app()->forgetInstance(CommandIndexService::class);

    $response = $this->get(route('command-index'));

    $response->assertOk()
        ->assertSeeText('Cached Section')
        ->assertSeeText('cached:run');
});

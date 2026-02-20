<?php

declare(strict_types=1);

use App\Services\DocsOutputCacheService;
use App\Services\DocsPathService;
use App\Services\DocumentService;
use App\Services\TocParserService;

beforeEach(function (): void {
    config()->set('docs.cache.path', storage_path('framework/cache/docs-output-tests/'.uniqid('', true)));

    app()->forgetInstance(DocsOutputCacheService::class);
    app(DocsOutputCacheService::class)->clear();
});

it('renders grouped sidebar navigation from documentation toc', function (): void {
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(TocParserService::class);
    app()->forgetInstance(DocumentService::class);

    $response = $this->get('/');

    $response->assertOk()
        ->assertSeeInOrder([
            'Guides',
            'Introduction',
            'Installation',
            'Zero to Deploy',
            'References',
            'Managing Sites',
            'Managing Servers',
            'Managing Services',
            'Managing Databases',
            'Cloud Providers',
            'Automation & AI',
            'Index',
            'Command Index',
        ])
        ->assertSee('href="'.route('home').'"', false)
        ->assertSee('href="'.route('docs.show', ['page' => 'installation']).'"', false)
        ->assertSee('href="'.route('docs.show', ['page' => 'managing-services']).'"', false);

    $content = $response->getContent();

    expect($content)->not->toBeFalse();
    expect((string) $content)
        ->toMatch('/<a[^>]*href="'.preg_quote(route('command-index'), '/').'"[^>]*wire:navigate(?:="")?[^>]*>/')
        ->not->toMatch('/<a[^>]*href="'.preg_quote(route('command-index'), '/').'"[^>]*target="_blank"[^>]*>/');
});

it('redirects missing docs pages to the docs home', function (): void {
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(TocParserService::class);
    app()->forgetInstance(DocumentService::class);

    $this->get('/docs/introduction')
        ->assertRedirect('/')
        ->assertStatus(301);
});

it('uses cached docs viewer payload when available', function (): void {
    $docsOutputCache = app(DocsOutputCacheService::class);
    $unresolvableDocsPath = storage_path('framework/testing/non-existent-docs-'.uniqid('', true));

    config()->set('docs.path', $unresolvableDocsPath);
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(TocParserService::class);
    app()->forgetInstance(DocumentService::class);

    $docsOutputCache->put(
        $docsOutputCache->key('docs-viewer', ['home']),
        [
            'page' => '',
            'title' => 'Cached Docs Title',
            'content' => '<h1>Cached Docs Title</h1><p>Cached body.</p>',
            'headings' => [],
            'toc' => [
                [
                    'name' => 'Cached Section',
                    'anchor' => 'cached-section',
                    'links' => [
                        [
                            'title' => 'Cached Link',
                            'path' => 'cached-link',
                        ],
                    ],
                ],
            ],
        ],
    );

    $response = $this->get('/');

    $response->assertOk()
        ->assertSeeText('Cached Docs Title')
        ->assertSeeText('Cached Section')
        ->assertSeeText('Cached Link');
});

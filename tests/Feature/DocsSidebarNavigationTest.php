<?php

declare(strict_types=1);

use App\Services\DocsPathService;
use App\Services\DocumentService;
use App\Services\TocParserService;

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
        ])
        ->assertSee('href="'.route('home').'"', false)
        ->assertSee('href="'.route('docs.show', ['page' => 'installation']).'"', false)
        ->assertSee('href="'.route('docs.show', ['page' => 'managing-services']).'"', false);
});

it('redirects missing docs pages to the docs home', function (): void {
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(TocParserService::class);
    app()->forgetInstance(DocumentService::class);

    $this->get('/docs/introduction')
        ->assertRedirect('/')
        ->assertStatus(301);
});

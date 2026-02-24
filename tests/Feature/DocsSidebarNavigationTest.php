<?php

declare(strict_types=1);

use App\Services\DocsPathService;
use App\Services\DocumentService;
use App\Services\TocParserService;
use Tests\Concerns\UsesDocsFixtures;

uses(UsesDocsFixtures::class);

beforeEach(function (): void {
    $this->configureDocsFixtures();
});

it('renders fixture toc links on docs home', function (): void {
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(TocParserService::class);
    app()->forgetInstance(DocumentService::class);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSeeText('Guides')
        ->assertSeeText('Installation')
        ->assertSee('href="'.route('docs.show', ['page' => 'installation']).'"', false)
        ->assertSee('href="'.route('command-index').'"', false);
});

it('renders a fixture docs page', function (): void {
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(TocParserService::class);
    app()->forgetInstance(DocumentService::class);

    $response = $this->get(route('docs.show', ['page' => 'installation']));

    $response->assertOk()
        ->assertSeeText('Installation')
        ->assertSeeText('Requirements');
});

it('converts relative markdown docs links to internal routes', function (): void {
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(TocParserService::class);
    app()->forgetInstance(DocumentService::class);

    $response = $this->get(route('docs.show', ['page' => 'documentation']));

    $response->assertOk()
        ->assertSee('href="'.route('home').'"', false)
        ->assertSee('href="'.route('docs.show', ['page' => 'installation']).'"', false)
        ->assertSee('href="'.route('docs.show', ['page' => 'link-behavior']).'"', false)
        ->assertSee('href="https://github.com/loadinglucian/deployer-php/blob/main/docs/operations/runbooks.md" target="_blank" rel="noopener noreferrer"', false);
});

it('redirects missing docs pages to the docs home', function (): void {
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(TocParserService::class);
    app()->forgetInstance(DocumentService::class);

    $this->get(route('docs.show', ['page' => 'does-not-exist']))
        ->assertRedirect(route('home'))
        ->assertMovedPermanently();
});

<?php

declare(strict_types=1);

use App\Services\CommandIndexService;
use App\Services\DocsPathService;
use App\Services\TocParserService;
use Illuminate\Support\Facades\Cache;

it('renders the command index with discovered commands', function (): void {
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(CommandIndexService::class);
    Cache::forget('command_index:v2');

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
    Cache::forget('command_index:v2');

    $response = $this->get(route('command-index'));

    $response->assertOk()
        ->assertSeeText('site:delete')
        ->assertDontSee(route('docs.show', ['page' => 'managing-sites']).'#deleting-a-site', false);
});

it('renders the empty-state message when no commands are available', function (): void {
    Cache::forget('command_index:v2');

    Cache::put('command_index:v2', [
        'sections' => [],
        'groups' => [],
        'commandCount' => 0,
        'aliasCount' => 0,
    ], now()->addMinutes(5));

    $response = $this->get(route('command-index'));

    $response->assertOk()
        ->assertSeeText('No commands were discovered for the configured docs path.');
});

it('renders the docs-style single sidebar layout', function (): void {
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(TocParserService::class);
    app()->forgetInstance(CommandIndexService::class);
    Cache::forget('command_index:v2');

    $response = $this->get(route('command-index'));

    $response->assertOk()
        ->assertSee('href="'.route('home').'"', false)
        ->assertSeeText('Guides')
        ->assertSee('lg:grid-cols-[280px_1fr]', false)
        ->assertDontSee('lg:grid-cols-[280px_1fr_280px]', false);
});

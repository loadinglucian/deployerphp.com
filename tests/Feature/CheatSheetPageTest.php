<?php

declare(strict_types=1);

use App\Services\CommandCheatSheetService;
use App\Services\DocsPathService;
use Illuminate\Support\Facades\Cache;

it('renders the command cheat sheet with discovered commands', function (): void {
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(CommandCheatSheetService::class);
    Cache::forget('cheat_sheet:v2');

    $response = $this->get(route('cheat-sheet'));

    $response->assertOk()
        ->assertSee('Command Cheat Sheet')
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
    app()->forgetInstance(CommandCheatSheetService::class);
    Cache::forget('cheat_sheet:v2');

    $response = $this->get(route('cheat-sheet'));

    $response->assertOk()
        ->assertSeeText('site:delete')
        ->assertDontSee(route('docs.show', ['page' => 'managing-sites']).'#deleting-a-site', false);
});

it('renders the empty-state message when no commands are available', function (): void {
    Cache::forget('cheat_sheet:v2');

    Cache::put('cheat_sheet:v2', [
        'sections' => [],
        'groups' => [],
        'commandCount' => 0,
        'aliasCount' => 0,
    ], now()->addMinutes(5));

    $response = $this->get(route('cheat-sheet'));

    $response->assertOk()
        ->assertSeeText('No commands were discovered for the configured docs path.');
});

<?php

declare(strict_types=1);

use App\Services\CommandCheatSheetService;
use App\Services\DocsPathService;

it('renders the command cheat sheet with discovered commands', function (): void {
    ensureDocsPathConfigured();

    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(CommandCheatSheetService::class);

    $response = $this->get(route('cheat-sheet'));

    $response->assertOk()
        ->assertSee('Command Cheat Sheet')
        ->assertSee('server:add')
        ->assertSee('site:deploy');
});

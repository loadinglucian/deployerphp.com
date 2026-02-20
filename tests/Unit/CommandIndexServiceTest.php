<?php

declare(strict_types=1);

use App\Services\CommandIndexService;
use App\Services\DocsPathService;
use Tests\Concerns\UsesDocsFixtures;

uses(UsesDocsFixtures::class);

beforeEach(function (): void {
    $this->configureDocsFixtures();
});

it('builds grouped command index data from fixture command classes', function (): void {
    $index = app(CommandIndexService::class)->build();

    expect($index['commandCount'])->toBe(3)
        ->and($index['aliasCount'])->toBe(1)
        ->and($index['groups'])->toHaveCount(3)
        ->and($index['groups'][0]['name'])->toBe('AWS')
        ->and($index['groups'][1]['name'])->toBe('Custom-task')
        ->and($index['groups'][2]['name'])->toBe('Server');

    $commands = collect($index['groups'])->pluck('commands')->flatten(1)->pluck('primary')->all();

    expect($commands)->toBe([
        'aws:region',
        'custom-task',
        'server:add',
    ]);
});

it('maps namespaces into ordered related sections', function (): void {
    $sections = app(CommandIndexService::class)->build()['sections'];

    expect(array_column($sections, 'name'))->toBe([
        'Server & Site Operations',
        'Cloud Providers',
        'Other',
    ]);
});

it('falls back to vendor console commands when docs console path is unavailable', function (): void {
    config()->set('docs.path', storage_path('framework/testing/no-console-'.uniqid('', true)));
    app()->forgetInstance(DocsPathService::class);
    app()->forgetInstance(CommandIndexService::class);

    $index = app(CommandIndexService::class)->build();

    expect($index['commandCount'])->toBeGreaterThan(0)
        ->and($index['sections'])->not->toBeEmpty()
        ->and($index['groups'])->not->toBeEmpty();
});

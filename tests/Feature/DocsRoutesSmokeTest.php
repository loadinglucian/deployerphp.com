<?php

declare(strict_types=1);

use Tests\Concerns\UsesDocsFixtures;

uses(UsesDocsFixtures::class);

beforeEach(function (): void {
    $this->configureDocsFixtures();
});

it('exposes the docs home route', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSeeText('Fixture Documentation Home');
});

it('exposes the command index route', function (): void {
    $this->get(route('command-index'))
        ->assertOk()
        ->assertSeeText('Command Index');
});

it('redirects docs index routes to home', function (string $uri): void {
    $this->get($uri)
        ->assertRedirect(route('home'))
        ->assertMovedPermanently();
})->with([
    '/docs',
    '/docs/',
]);

<?php

declare(strict_types=1);

namespace Tests\Fixtures\Docs\App\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'server:add|server:create', description: 'Create a server.')]
final class FakeServerCommand {}

<?php

declare(strict_types=1);

namespace Tests\Fixtures\Docs\App\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'custom-task', description: 'Run a custom task.')]
final class FakeOtherCommand {}

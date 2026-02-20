<?php

declare(strict_types=1);

namespace Tests\Fixtures\Docs\App\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'aws:region', description: 'List AWS regions.')]
final class FakeCloudCommand {}

<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DocsOutputCacheService;
use Illuminate\Console\Command;

final class DocsClearCommand extends Command
{
    protected $signature = 'docs:clear';

    protected $description = 'Clear docs output files';

    public function handle(DocsOutputCacheService $docsOutput): int
    {
        $docsOutput->clear();

        $this->components->info('Docs output cleared.');

        return self::SUCCESS;
    }
}

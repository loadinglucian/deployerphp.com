<?php

declare(strict_types=1);

namespace App\Services;

final readonly class DocsPathService
{
    /**
     * Get the absolute path to the documentation directory.
     */
    public function path(): string
    {
        /** @var string $path */
        $path = config('docs.path');

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return base_path($path);
    }
}

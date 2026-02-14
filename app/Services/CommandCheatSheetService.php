<?php

declare(strict_types=1);

namespace App\Services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final readonly class CommandCheatSheetService
{
    public function __construct(
        private DocsPathService $docsPath,
    ) {}

    /**
     * @return array{
     *     groups: array<int, array{
     *         name: string,
     *         count: int,
     *         commands: array<int, array{
     *             primary: string,
     *             aliases: array<int, string>,
     *             description: string,
     *             links: array<int, array{page: string, anchor: string}>
     *         }>
     *     }>,
     *     commandCount: int,
     *     aliasCount: int
     * }
     */
    public function build(): array
    {
        $commands = $this->discoverCommands();
        $links = $this->resolveCommandLinks();
        $groups = [];
        $aliasCount = 0;

        foreach ($commands as $command) {
            $command['links'] = $links[$command['primary']] ?? [];
            $namespace = $this->extractNamespace($command['primary']);

            if (! array_key_exists($namespace, $groups)) {
                $groups[$namespace] = [
                    'name' => ucfirst($namespace),
                    'count' => 0,
                    'commands' => [],
                ];
            }

            $groups[$namespace]['commands'][] = $command;
            $groups[$namespace]['count']++;
            $aliasCount += count($command['aliases']);
        }

        ksort($groups);

        return [
            'groups' => array_values($groups),
            'commandCount' => count($commands),
            'aliasCount' => $aliasCount,
        ];
    }

    /**
     * @return array<int, array{
     *     primary: string,
     *     aliases: array<int, string>,
     *     description: string,
     *     links?: array<int, array{page: string, anchor: string}>
     * }>
     */
    private function discoverCommands(): array
    {
        $consolePath = $this->resolveConsolePath();

        if ($consolePath === null) {
            return [];
        }

        $commands = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($consolePath));

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if (! str_ends_with($file->getFilename(), 'Command.php')) {
                continue;
            }

            $realPath = $file->getRealPath();

            if ($realPath === false) {
                continue;
            }

            $metadata = $this->parseCommandMetadata($realPath);

            if ($metadata === null) {
                continue;
            }

            $commands[] = $metadata;
        }

        usort(
            $commands,
            static fn (array $left, array $right): int => $left['primary'] <=> $right['primary'],
        );

        return $commands;
    }

    private function resolveConsolePath(): ?string
    {
        $docsPath = $this->docsPath->path();

        $candidates = [
            dirname($docsPath).'/app/Console',
            base_path('vendor/loadinglucian/deployer-php/app/Console'),
        ];

        foreach ($candidates as $candidate) {
            if (is_dir($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return array{primary: string, aliases: array<int, string>, description: string}|null
     */
    private function parseCommandMetadata(string $path): ?array
    {
        $content = file_get_contents($path);

        if ($content === false) {
            return null;
        }

        if (preg_match('/#\[AsCommand\((.*?)\)\]/s', $content, $attributeMatches) !== 1) {
            return null;
        }

        $attribute = $attributeMatches[1];

        if (preg_match("/name:\\s*'([^']+)'/", $attribute, $nameMatches) !== 1) {
            return null;
        }

        preg_match("/description:\\s*'([^']+)'/", $attribute, $descriptionMatches);

        $names = array_values(
            array_filter(
                array_map(
                    trim(...),
                    explode('|', $nameMatches[1])
                ),
                static fn (string $name): bool => $name !== '',
            ),
        );

        $primary = array_shift($names);

        if ($primary === null) {
            return null;
        }

        return [
            'primary' => $primary,
            'aliases' => $names,
            'description' => $descriptionMatches[1] ?? '',
        ];
    }

    /**
     * Scan documentation markdown files to map commands to their doc sections.
     *
     * @return array<string, array<int, array{page: string, anchor: string}>>
     */
    private function resolveCommandLinks(): array
    {
        $docsPath = $this->docsPath->path();

        if (! is_dir($docsPath)) {
            return [];
        }

        $files = glob($docsPath.'/*.md');

        if ($files === false) {
            return [];
        }

        sort($files);

        /** @var array<string, array<string, array{page: string, anchor: string}>> $seen */
        $seen = [];

        foreach ($files as $file) {
            $page = basename($file, '.md');

            if ($page === 'documentation' || $page === 'README') {
                continue;
            }

            $content = file_get_contents($file);

            if ($content === false) {
                continue;
            }

            $currentAnchor = null;

            foreach (explode("\n", $content) as $line) {
                if (preg_match('/^#{2,3}\s+(.+)$/', $line, $headingMatch) === 1) {
                    $currentAnchor = $this->slugify($headingMatch[1]);
                }

                if ($currentAnchor === null) {
                    continue;
                }

                if (preg_match_all('/deployer\s+([a-z][a-z0-9:]+)\b/', $line, $commandMatches) !== 0) {
                    foreach ($commandMatches[1] as $commandName) {
                        $key = $page.'#'.$currentAnchor;

                        if (! array_key_exists($commandName, $seen)) {
                            $seen[$commandName] = [];
                        }

                        $seen[$commandName][$key] = [
                            'page' => $page,
                            'anchor' => $currentAnchor,
                        ];
                    }
                }
            }
        }

        $links = [];

        foreach ($seen as $commandName => $references) {
            $links[$commandName] = array_values($references);
        }

        return $links;
    }

    private function slugify(string $text): string
    {
        $slug = mb_strtolower($text);
        $slug = (string) preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = (string) preg_replace('/[\s-]+/', '-', $slug);

        return trim($slug, '-');
    }

    private function extractNamespace(string $command): string
    {
        $segments = explode(':', $command, 2);

        if ($segments[0] === '') {
            return 'general';
        }

        return $segments[0];
    }
}

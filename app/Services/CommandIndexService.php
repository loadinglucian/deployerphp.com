<?php

declare(strict_types=1);

namespace App\Services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final readonly class CommandIndexService
{
    public function __construct(
        private DocsPathService $docsPath,
    ) {}

    /**
     * @return array{
     *     sections: array<int, array{
     *         name: string,
     *         count: int,
     *         namespaceCount: int,
     *         groups: array<int, array{
     *             name: string,
     *             count: int,
     *             commands: array<int, array{
     *                 primary: string,
     *                 aliases: array<int, string>,
     *                 description: string
     *             }>
     *         }>
     *     }>,
     *     groups: array<int, array{
     *         name: string,
     *         count: int,
     *         commands: array<int, array{
     *             primary: string,
     *             aliases: array<int, string>,
     *             description: string
     *         }>
     *     }>,
     *     commandCount: int,
     *     aliasCount: int
     * }
     */
    public function build(): array
    {
        $commands = $this->discoverCommands();
        $groups = [];
        $aliasCount = 0;

        foreach ($commands as $command) {
            $namespace = $this->extractNamespace($command['primary']);

            if (! array_key_exists($namespace, $groups)) {
                $groups[$namespace] = [
                    'name' => $this->formatNamespaceName($namespace),
                    'count' => 0,
                    'commands' => [],
                ];
            }

            $groups[$namespace]['commands'][] = $command;
            $groups[$namespace]['count']++;
            $aliasCount += count($command['aliases']);
        }

        ksort($groups);
        $sections = $this->buildRelatedSections($groups);

        return [
            'sections' => $sections,
            'groups' => array_values($groups),
            'commandCount' => count($commands),
            'aliasCount' => $aliasCount,
        ];
    }

    /**
     * @return array<int, array{
     *     primary: string,
     *     aliases: array<int, string>,
     *     description: string
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

        if (preg_match('/name:\s*(?:\'([^\']+)\'|"([^"]+)")/', $attribute, $nameMatches) !== 1) {
            return null;
        }

        $commandNames = $nameMatches[1] !== '' ? $nameMatches[1] : $nameMatches[2];
        $description = '';

        if (preg_match('/description:\s*(?:\'([^\']+)\'|"([^"]+)")/', $attribute, $descriptionMatches) === 1) {
            $description = $descriptionMatches[1] !== '' ? $descriptionMatches[1] : $descriptionMatches[2];
        }

        $names = array_values(
            array_filter(
                array_map(
                    trim(...),
                    explode('|', $commandNames)
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
            'description' => $description,
        ];
    }

    private function extractNamespace(string $command): string
    {
        $segments = explode(':', $command, 2);

        if ($segments[0] === '') {
            return 'general';
        }

        return $segments[0];
    }

    private function formatNamespaceName(string $namespace): string
    {
        return match ($namespace) {
            'aws' => 'AWS',
            'cf' => 'Cloudflare',
            'do' => 'DigitalOcean',
            'mariadb' => 'MariaDB',
            'php' => 'PHP',
            'postgresql' => 'PostgreSQL',
            default => ucfirst($namespace),
        };
    }

    /**
     * @param array<string, array{
     *     name: string,
     *     count: int,
     *     commands: array<int, array{
     *         primary: string,
     *         aliases: array<int, string>,
     *         description: string
     *     }>
     * }> $groups
     * @return array<int, array{
     *     name: string,
     *     count: int,
     *     namespaceCount: int,
     *     groups: array<int, array{
     *         name: string,
     *         count: int,
     *         commands: array<int, array{
     *             primary: string,
     *             aliases: array<int, string>,
     *             description: string
     *         }>
     *     }>
     * }>
     */
    private function buildRelatedSections(array $groups): array
    {
        /** @var array<string, array{
         *     name: string,
         *     count: int,
         *     namespaceCount: int,
         *     groups: array<int, array{
         *         name: string,
         *         count: int,
         *         commands: array<int, array{
         *             primary: string,
         *             aliases: array<int, string>,
         *             description: string
         *         }>
         *     }>
         * }> $sections
         */
        $sections = [];

        foreach ($groups as $namespace => $group) {
            $sectionName = $this->resolveNamespaceSection($namespace);

            if (! array_key_exists($sectionName, $sections)) {
                $sections[$sectionName] = [
                    'name' => $sectionName,
                    'count' => 0,
                    'namespaceCount' => 0,
                    'groups' => [],
                ];
            }

            $sections[$sectionName]['groups'][] = $group;
            $sections[$sectionName]['count'] += $group['count'];
            $sections[$sectionName]['namespaceCount']++;
        }

        $orderedSections = [];

        foreach ($this->sectionOrder() as $sectionName) {
            if (! array_key_exists($sectionName, $sections)) {
                continue;
            }

            $orderedSections[] = $sections[$sectionName];
            unset($sections[$sectionName]);
        }

        if ($sections !== []) {
            foreach ($sections as $section) {
                $orderedSections[] = $section;
            }
        }

        return $orderedSections;
    }

    private function resolveNamespaceSection(string $namespace): string
    {
        return match ($namespace) {
            'server', 'site' => 'Server & Site Operations',
            'cron', 'supervisor' => 'Scheduling & Process Control',
            'nginx', 'php' => 'Web Runtime Services',
            'mariadb', 'postgresql', 'redis', 'memcached' => 'Data Services',
            'scaffold' => 'Scaffolding',
            'aws', 'cf', 'do' => 'Cloud Providers',
            default => 'Other',
        };
    }

    /**
     * @return array<int, string>
     */
    private function sectionOrder(): array
    {
        return [
            'Server & Site Operations',
            'Scheduling & Process Control',
            'Web Runtime Services',
            'Data Services',
            'Scaffolding',
            'Cloud Providers',
            'Other',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\CommandIndexService;
use App\Services\TocParserService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.command-index')]
final class CommandIndex extends Component
{
    /**
     * @var array<int, array{
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
    public array $sections = [];

    /**
     * @var array<int, array{
     *     name: string,
     *     count: int,
     *     commands: array<int, array{
     *         primary: string,
     *         aliases: array<int, string>,
     *         description: string
     *     }>
     * }>
     */
    public array $groups = [];

    public int $commandCount = 0;

    public int $aliasCount = 0;

    /**
     * @var array<int, array{
     *     name: string,
     *     anchor: string,
     *     links: array<int, array{title: string, path: string}>
     * }>
     */
    public array $toc = [];

    public function mount(TocParserService $tocParser, CommandIndexService $sheet): void
    {
        $this->toc = $tocParser->parse();

        $cacheTtlSeconds = (int) config('docs.command_index.cache_ttl_seconds', 300);
        $cacheEnabled = ! app()->isLocal() && $cacheTtlSeconds > 0;

        /** @var array{
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
         * } $commandIndex
         */
        $commandIndex = $cacheEnabled
            ? Cache::remember(
                'command_index:v2',
                now()->addSeconds($cacheTtlSeconds),
                fn (): array => $sheet->build(),
            )
            : $sheet->build();

        $this->sections = $commandIndex['sections'];
        $this->groups = $commandIndex['groups'];
        $this->commandCount = $commandIndex['commandCount'];
        $this->aliasCount = $commandIndex['aliasCount'];
    }

    public function render(): View
    {
        return view('livewire.command-index');
    }
}

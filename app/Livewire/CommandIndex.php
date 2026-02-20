<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\CommandIndexService;
use App\Services\DocsOutputCacheService;
use App\Services\TocParserService;
use Illuminate\Contracts\View\View;
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

    public function mount(
        DocsOutputCacheService $docsOutputCache,
        TocParserService $tocParser,
        CommandIndexService $sheet,
    ): void {
        /** @var array{
         *     toc: array<int, array{
         *         name: string,
         *         anchor: string,
         *         links: array<int, array{title: string, path: string}>
         *     }>,
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
         * } $payload
         */
        $payload = $docsOutputCache->remember(
            $docsOutputCache->key('command-index'),
            function () use ($tocParser, $sheet): array {
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
                $commandIndex = $sheet->build();

                return [
                    'toc' => $tocParser->parse(),
                    'sections' => $commandIndex['sections'],
                    'groups' => $commandIndex['groups'],
                    'commandCount' => $commandIndex['commandCount'],
                    'aliasCount' => $commandIndex['aliasCount'],
                ];
            },
        );

        $this->hydrateFromPayload($payload);
    }

    public function render(): View
    {
        return view('livewire.command-index');
    }

    /**
     * @param  array{
     *     toc: array<int, array{
     *         name: string,
     *         anchor: string,
     *         links: array<int, array{title: string, path: string}>
     *     }>,
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
     * } $payload
     */
    private function hydrateFromPayload(array $payload): void
    {
        $this->toc = $payload['toc'];
        $this->sections = $payload['sections'];
        $this->groups = $payload['groups'];
        $this->commandCount = $payload['commandCount'];
        $this->aliasCount = $payload['aliasCount'];
    }
}

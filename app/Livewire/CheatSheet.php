<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\CommandCheatSheetService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.cheat-sheet')]
final class CheatSheet extends Component
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

    public function mount(CommandCheatSheetService $sheet): void
    {
        $cacheTtlSeconds = (int) config('docs.cheat_sheet.cache_ttl_seconds', 300);
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
         * } $cheatSheet
         */
        $cheatSheet = $cacheEnabled
            ? Cache::remember(
                'cheat_sheet:v2',
                now()->addSeconds($cacheTtlSeconds),
                fn (): array => $sheet->build(),
            )
            : $sheet->build();

        $this->sections = $cheatSheet['sections'];
        $this->groups = $cheatSheet['groups'];
        $this->commandCount = $cheatSheet['commandCount'];
        $this->aliasCount = $cheatSheet['aliasCount'];
    }

    public function render(): View
    {
        return view('livewire.cheat-sheet');
    }
}

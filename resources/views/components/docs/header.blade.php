<flux:header sticky container class="z-11 border-b border-zinc-200 bg-white py-3 dark:border-zinc-800 dark:bg-zinc-900">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-3" inset="left" />

    {{-- Colorful Logo --}}
    <a href="{{ route('home') }}" wire:navigate class="mr-4 flex min-w-0 flex-1 items-center gap-3 font-mono">
        <img src="{{ asset('logo-mark.svg') }}" alt="" class="size-7 shrink-0 dark:hidden" aria-hidden="true" />
        <img src="{{ asset('logo-mark-dark.svg') }}" alt="" class="hidden size-7 shrink-0 dark:block" aria-hidden="true" />
        <span class="font-sans text-lg font-semibold text-cyan-400">DeployerPHP</span>
        <span class="hidden min-w-0 flex-1 gap-0 sm:flex">
            <span class="h-px flex-1 bg-cyan-400"></span>
            <span class="h-px flex-1 bg-blue-400"></span>
            <span class="h-px flex-1 bg-fuchsia-400"></span>
            <span class="h-px flex-1 bg-slate-500 dark:bg-slate-600"></span>
        </span>
    </a>

    <div class="flex items-center gap-3">
        {{-- GitHub Button --}}
        <flux:button icon="github" size="sm" href="https://github.com/loadinglucian/deployer-php/" target="_blank">GitHub</flux:button>

        <flux:separator vertical class="my-2 ml-3" />

        {{-- Dark Mode Toggle --}}
        <flux:button x-data variant="subtle" square aria-label="Toggle color scheme" x-on:click="$flux.appearance = $flux.dark ? 'light' : 'dark'">
            <flux:icon.moon x-cloak x-show="! $flux.dark" class="size-5" />
            <flux:icon.sun x-cloak x-show="$flux.dark" class="size-5" />
        </flux:button>
    </div>
</flux:header>

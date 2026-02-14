<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>DeployerPHP - Command Cheat Sheet</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('logo-mark.svg') }}" />
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-white text-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
        {{-- Header (matches docs layout) --}}
        <flux:header sticky container class="z-11 border-b border-zinc-200 bg-white py-3 dark:border-zinc-800 dark:bg-zinc-900">
            <a href="{{ route('home') }}" class="mr-4 flex min-w-0 flex-1 items-center gap-3 font-mono">
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
                <flux:button size="sm" href="{{ route('cheat-sheet') }}" variant="primary">Cheat Sheet</flux:button>
                <flux:button icon="github" size="sm" href="https://github.com/loadinglucian/deployer-php/" target="_blank">GitHub</flux:button>

                <flux:separator vertical class="my-2 ml-3" />

                <flux:button x-data variant="subtle" square aria-label="Toggle color scheme" x-on:click="$flux.appearance = $flux.dark ? 'light' : 'dark'">
                    <flux:icon.moon x-cloak x-show="! $flux.dark" class="size-5" />
                    <flux:icon.sun x-cloak x-show="$flux.dark" class="size-5" />
                </flux:button>
            </div>
        </flux:header>

        {{-- Main Content --}}
        <flux:main class="p-0!">
            <div class="mx-auto max-w-7xl px-6 py-8 sm:py-10 lg:px-8">
                {{-- Page Header --}}
                <header class="mb-8">
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-50">Command Cheat Sheet</h1>
                    <p class="mt-2 flex items-center gap-3 text-sm text-zinc-500 dark:text-zinc-400">
                        <span><strong class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $commandCount }}</strong> commands</span>
                        <span aria-hidden="true" class="text-zinc-300 dark:text-zinc-600">&middot;</span>
                        <span><strong class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $aliasCount }}</strong> aliases</span>
                    </p>
                </header>

                {{-- Global Options Hint --}}
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">
                    <span>Prefix commands with <code class="rounded bg-zinc-200/60 px-1.5 py-0.5 font-mono text-xs text-zinc-800 dark:bg-zinc-700/60 dark:text-zinc-200">deployer</code> (or your alias).</span>
                    <span class="flex flex-wrap items-center gap-1.5">
                        Global options:
                        <code class="rounded bg-zinc-200/60 px-1.5 py-0.5 font-mono text-xs text-zinc-800 dark:bg-zinc-700/60 dark:text-zinc-200">--env</code>
                        <code class="rounded bg-zinc-200/60 px-1.5 py-0.5 font-mono text-xs text-zinc-800 dark:bg-zinc-700/60 dark:text-zinc-200">--inventory</code>
                        <code class="rounded bg-zinc-200/60 px-1.5 py-0.5 font-mono text-xs text-zinc-800 dark:bg-zinc-700/60 dark:text-zinc-200">--quiet</code>
                    </span>
                </div>

                {{-- Command Groups Grid --}}
                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="All DeployerPHP commands">
                    @foreach ($groups as $group)
                        <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <h2 class="mb-3 flex items-baseline gap-1.5 text-xs font-semibold uppercase tracking-wide text-accent dark:text-blue-400">
                                {{ $group['name'] }}
                                <span class="text-zinc-400 dark:text-zinc-500">({{ $group['count'] }})</span>
                            </h2>

                            <ul class="space-y-1.5">
                                @foreach ($group['commands'] as $command)
                                    <li title="{{ $command['description'] }}">
                                        <code class="font-mono text-sm text-zinc-900 dark:text-zinc-100">{{ $command['primary'] }}</code>

                                        @if ($command['links'] !== [])
                                            @foreach ($command['links'] as $index => $link)
                                                <a href="{{ route('docs.show', ['page' => $link['page']]) }}#{{ $link['anchor'] }}" class="inline-flex items-center justify-center size-4 rounded-sm bg-accent/10 text-[10px] font-medium leading-none text-accent no-underline hover:bg-accent/20 dark:bg-blue-400/10 dark:text-blue-400 dark:hover:bg-blue-400/20" title="{{ $link['page'] }}#{{ $link['anchor'] }}">{{ $index + 1 }}</a>
                                            @endforeach
                                        @endif

                                        @if ($command['aliases'] !== [])
                                            <span class="ml-1 text-xs text-zinc-400 dark:text-zinc-500">
                                                alias:
                                                @foreach ($command['aliases'] as $alias)
                                                    <code class="font-mono text-fuchsia-600 dark:text-fuchsia-400">{{ $alias }}</code>
                                                @endforeach
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </article>
                    @endforeach
                </section>
            </div>

            {{-- Footer (matches docs layout) --}}
            <footer class="my-10 flex flex-col gap-6 p-6 text-center text-sm text-zinc-700/60 dark:border-zinc-800 dark:text-zinc-300/60">
                <div class="flex items-center justify-center gap-1">
                    <a href="https://github.com/loadinglucian/deployer-php/" target="_blank" rel="noopener" aria-label="View on GitHub" class="inline-flex items-center justify-center rounded-md p-2 text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                        <flux:icon.github class="size-5" />
                        <span class="sr-only">GitHub</span>
                    </a>

                    <a href="https://www.reddit.com/r/DeployerPHP/" target="_blank" rel="noopener" aria-label="Follow on Reddit" class="inline-flex items-center justify-center rounded-md p-2 text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                        <flux:icon.reddit class="size-5" />
                        <span class="sr-only">Reddit</span>
                    </a>

                    <a href="https://x.com/loadinglucian" target="_blank" rel="noopener" aria-label="Follow on X" class="inline-flex items-center justify-center rounded-md p-2 text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                        <flux:icon.x class="size-5" />
                        <span class="sr-only">X</span>
                    </a>
                </div>

                <p>
                    <span>Made with tender love</span>
                    <svg class="inline-flex shrink-0 text-zinc-400 [:where(&amp;)]:size-4" data-flux-icon="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                        <path d="M2 6.342a3.375 3.375 0 0 1 6-2.088 3.375 3.375 0 0 1 5.997 2.26c-.063 2.134-1.618 3.76-2.955 4.784a14.437 14.437 0 0 1-2.676 1.61c-.02.01-.038.017-.05.022l-.014.006-.004.002h-.002a.75.75 0 0 1-.592.001h-.002l-.004-.003-.015-.006a5.528 5.528 0 0 1-.232-.107 14.395 14.395 0 0 1-2.535-1.557C3.564 10.22 1.999 8.558 1.999 6.38L2 6.342Z"></path>
                    </svg>
                    <span>and care</span>
                    <a href="https://www.google.com/search?q=bucharest+romania" class="underline underline-offset-4" target="_blank" rel="noopener">in Bucharest, Romania</a>
                </p>
                <p>
                    &copy; {{ now()->year }}
                    <a href="https://x.com/loadinglucian" class="underline underline-offset-4" target="_blank" rel="noopener">Lucian V&#259;c&#259;roiu</a>
                </p>
            </footer>
        </flux:main>

        @fluxScripts
    </body>
</html>

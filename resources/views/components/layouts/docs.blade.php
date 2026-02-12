<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>DeployerPHP - {{ $title ?? 'Documentation' }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('logo-mark.svg') }}" />
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-white text-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
        {{--
            // ----
            // Fixed Header
            // ----
        --}}
        <flux:header sticky container class="z-11 border-b border-zinc-200 bg-white py-3 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-3" inset="left" />

            {{-- Colorful Logo --}}
            <a href="{{ route('home') }}" wire:navigate class="mr-4 flex min-w-0 flex-1 items-center gap-3 font-mono">
                <img src="{{ asset('logo-mark.svg') }}" alt="" class="size-7 shrink-0" aria-hidden="true" />
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
                <flux:button icon="github" size="sm" href="https://github.com/loadinglucian/deployer-php" target="_blank">GitHub</flux:button>

                <flux:separator vertical class="my-2 ml-3" />

                {{-- Dark Mode Toggle --}}
                <flux:button x-data variant="subtle" square aria-label="Toggle color scheme" x-on:click="$flux.appearance = $flux.dark ? 'light' : 'dark'">
                    <flux:icon.moon x-cloak x-show="! $flux.dark" class="size-5" />
                    <flux:icon.sun x-cloak x-show="$flux.dark" class="size-5" />
                </flux:button>
            </div>
        </flux:header>

        {{--
            // ----
            // Mobile Sidebar
            // ----
        --}}
        <flux:sidebar sticky stashable class="border-r border-zinc-200 bg-white px-6 pt-20 pb-10 lg:hidden dark:border-zinc-800 dark:bg-zinc-900">
            {{-- See toc.blade.php --}}
            {{ $sidebar }}
        </flux:sidebar>

        {{--
            // ----
            // Main Layout
            // ----
        --}}
        <flux:main class="p-0!">
            <div class="mx-auto max-w-7xl px-6 py-4 sm:py-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-[280px_1fr_280px]">
                    {{-- Left Sidebar: Table of Contents (desktop) --}}
                    <aside class="hidden self-stretch lg:block">
                        <div class="sticky max-h-[calc(100vh-var(--docs-sticky-top)-2rem)] w-54 overflow-y-auto pr-12" style="top: var(--docs-sticky-top)">
                            {{-- See toc.blade.php --}}
                            {{ $sidebar }}
                        </div>
                    </aside>

                    {{-- Main Content --}}
                    <main class="min-w-0 lg:max-w-[720px]">
                        {{-- See content.blade.php --}}
                        {{ $slot }}
                    </main>

                    {{-- Right Sidebar: Page Headings --}}
                    <aside class="hidden self-stretch lg:block">
                        <div class="sticky max-h-[calc(100vh-var(--docs-sticky-top)-2rem)] w-70 overflow-y-auto pl-10" style="top: var(--docs-sticky-top)">
                            {{-- See headings.blade.php --}}
                            {{ $aside }}
                        </div>
                    </aside>
                </div>
            </div>
        </flux:main>

        @fluxScripts
    </body>
</html>

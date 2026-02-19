<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>DeployerPHP - Command Index</title>
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
        <x-docs.header />

        {{--
            // ----
            // Mobile Sidebar
            // ----
        --}}
        <flux:sidebar sticky stashable class="border-r border-zinc-200 bg-white px-6 pt-20 pb-10 lg:hidden dark:border-zinc-800 dark:bg-zinc-900">
            {{ $sidebar }}
        </flux:sidebar>

        {{--
            // ----
            // Main Layout
            // ----
        --}}
        <flux:main class="p-0!">
            <div class="mx-auto max-w-7xl px-6 py-4 sm:py-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-[280px_1fr]">
                    <aside class="hidden self-stretch lg:block">
                        <div class="sticky max-h-[calc(100vh-var(--docs-sticky-top)-2rem)] w-54 overflow-y-auto pr-12" style="top: var(--docs-sticky-top)">
                            {{ $sidebar }}
                        </div>
                    </aside>

                    <main class="min-h-[calc(100vh-var(--docs-sticky-top)-2rem)] min-w-0">
                        {{ $slot }}

                        <x-docs.footer />
                    </main>
                </div>
            </div>
        </flux:main>

        @fluxScripts
    </body>
</html>

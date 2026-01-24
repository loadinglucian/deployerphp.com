<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" x-data :class="{ 'dark': $store.darkMode.on }">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>DeployerPHP {{ $title ?? 'Documentation' }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white text-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
        <div x-data="{ mobileMenuOpen: false }">
            {{--
                // ----
                // Fixed Header
                // ----
            --}}
            <header class="fixed inset-x-0 top-0 z-50 backdrop-blur supports-backdrop-filter:from-white/80 supports-backdrop-filter:to-zinc-50/80 dark:from-zinc-900 dark:to-zinc-950 dark:supports-backdrop-filter:from-zinc-900/80 dark:supports-backdrop-filter:to-zinc-950/80">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 p-4 sm:p-6">
                    {{--
                        //
                        // Left: Hamburger (mobile) + Logo
                        // ----
                    --}}
                    <div class="flex flex-1 items-center gap-4">
                        {{-- Hamburger Menu Button (mobile only) --}}
                        <button type="button" class="-ml-2 flex h-10 w-10 items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-accent lg:hidden dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-accent" @click="mobileMenuOpen = !mobileMenuOpen" :aria-expanded="mobileMenuOpen" aria-label="Toggle navigation menu">
                            <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            <svg x-show="mobileMenuOpen" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>

                        {{-- Logo --}}
                        <a href="{{ route('docs.index') }}" class="flex flex-1 items-center gap-2 font-mono">
                            <span class="text-lg font-bold text-cyan-400">DeployerPHP</span>
                            <span class="flex max-w-48 flex-1 text-xs tracking-tight">
                                <span class="h-[2px] flex-1 overflow-hidden bg-cyan-400"></span>
                                <span class="h-[2px] flex-1 overflow-hidden bg-blue-400"></span>
                                <span class="h-[2px] flex-1 overflow-hidden bg-fuchsia-400"></span>
                                <span class="h-[2px] flex-1 overflow-hidden bg-slate-500 dark:bg-slate-600"></span>
                            </span>
                        </a>
                    </div>

                    {{--
                        //
                        // Right: Dark Mode Toggle
                        // ----
                    --}}
                    <div class="flex items-center gap-2">
                        <button type="button" class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-accent dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-accent" @click="$store.darkMode.toggle()" :aria-label="$store.darkMode.on ? 'Switch to light mode' : 'Switch to dark mode'">
                            {{-- Sun icon (shown in dark mode) --}}
                            <svg x-show="$store.darkMode.on" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                            </svg>
                            {{-- Moon icon (shown in light mode) --}}
                            <svg x-show="!$store.darkMode.on" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </header>

            {{--
                // ----
                // Mobile Overalay & Sidebar
                // ----
            --}}

            <div x-show="mobileMenuOpen" x-transition:enter="transition-opacity duration-300 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-200 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-zinc-900/50 lg:hidden" @click="mobileMenuOpen = false" x-cloak></div>

            <aside
                x-show="mobileMenuOpen"
                x-transition:enter="transition-transform duration-300 ease-out"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition-transform duration-200 ease-in"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="fixed inset-y-0 left-0 z-50 w-72 overflow-y-auto border-r border-zinc-200 bg-white px-6 pt-20 pb-12 lg:hidden dark:border-zinc-800 dark:bg-zinc-900"
                @click.away="mobileMenuOpen = false"
                @keydown.escape.window="mobileMenuOpen = false"
                x-cloak
            >
                {{-- See toc.blade.php --}}
                {{ $sidebar }}
            </aside>

            {{--
                // ----
                // Main Layout
                // ----
            --}}

            <div class="mx-auto max-w-7xl p-4 sm:p-6">
                <div class="py-20 lg:grid lg:grid-cols-[280px_1fr_280px]">
                    {{-- Left Sidebar: Table of Contents (desktop) --}}
                    <aside class="hidden self-stretch lg:block">
                        <div class="sticky max-h-[calc(100vh-var(--docs-sticky-top)-2rem)] w-54 overflow-y-auto pr-16" style="top: var(--docs-sticky-top)">
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
                        <div class="sticky max-h-[calc(100vh-var(--docs-sticky-top)-2rem)] w-70 overflow-y-auto pl-20" style="top: var(--docs-sticky-top)">
                            {{-- See headings.blade.php --}}
                            {{ $aside }}
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </body>
</html>

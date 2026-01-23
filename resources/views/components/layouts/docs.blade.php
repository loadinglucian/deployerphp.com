<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" x-data="{
    darkMode:
        localStorage.getItem('darkMode') === 'true' ||
        (! localStorage.getItem('darkMode') &&
            window.matchMedia('(prefers-color-scheme: dark)').matches),
}" x-init="$watch('darkMode', (val) => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>DeployerPHP {{ $title ?? 'Documentation' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white text-slate-900 dark:bg-slate-900 dark:text-slate-100">
        <div x-data="{ mobileMenuOpen: false }">
            {{-- Fixed Header --}}
            <header class="fixed inset-x-0 top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur supports-backdrop-filter:bg-white/80 dark:border-slate-700 dark:bg-slate-900/95 dark:supports-backdrop-filter:bg-slate-900/80">
                <div class="mx-auto flex h-14 max-w-screen-2xl items-center justify-between gap-4 px-4 sm:px-6">
                    {{-- Left: Hamburger (mobile) + Logo --}}
                    <div class="flex items-center gap-4">
                        {{-- Hamburger Menu Button (mobile only) --}}
                        <button type="button" class="-ml-2 flex h-10 w-10 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 lg:hidden dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200" @click="mobileMenuOpen = !mobileMenuOpen" :aria-expanded="mobileMenuOpen" aria-label="Toggle navigation menu">
                            <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            <svg x-show="mobileMenuOpen" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>

                        {{-- Logo --}}
                        <a href="{{ route('docs.index') }}" class="flex items-center gap-2 font-mono">
                            <span class="text-lg font-bold text-cyan-500">DeployerPHP</span>
                            <span class="hidden text-xs tracking-tight sm:flex">
                                <span class="text-cyan-500">━━</span>
                                <span class="text-blue-400">━━</span>
                                <span class="text-fuchsia-500">━━</span>
                                <span class="text-slate-400 dark:text-slate-500">━━</span>
                            </span>
                        </a>
                    </div>

                    {{-- Right: Dark Mode Toggle --}}
                    <div class="flex items-center gap-2">
                        <button type="button" class="flex h-10 w-10 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200" @click="darkMode = !darkMode" :aria-label="darkMode ? 'Switch to light mode' : 'Switch to dark mode'">
                            {{-- Sun icon (shown in dark mode) --}}
                            <svg x-show="darkMode" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                            </svg>
                            {{-- Moon icon (shown in light mode) --}}
                            <svg x-show="!darkMode" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </header>

            {{-- Mobile Sidebar Overlay --}}
            <div x-show="mobileMenuOpen" x-transition:enter="transition-opacity duration-300 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-200 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden" @click="mobileMenuOpen = false" x-cloak></div>

            {{-- Mobile Sidebar --}}
            <aside
                x-show="mobileMenuOpen"
                x-transition:enter="transition-transform duration-300 ease-out"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition-transform duration-200 ease-in"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="fixed inset-y-0 left-0 z-50 w-72 overflow-y-auto border-r border-slate-200 bg-white px-6 pt-20 pb-6 lg:hidden dark:border-slate-700 dark:bg-slate-900"
                @click.away="mobileMenuOpen = false"
                @keydown.escape.window="mobileMenuOpen = false"
                x-cloak
            >
                {{ $sidebar }}
            </aside>

            {{-- Main Layout --}}
            <div class="mx-auto max-w-screen-2xl pt-14">
                <div class="lg:flex">
                    {{-- Left Sidebar: Table of Contents (desktop) --}}
                    <aside class="hidden lg:block lg:w-64 lg:shrink-0 xl:w-72">
                        <div class="sticky top-14 h-[calc(100vh-3.5rem)] overflow-y-auto px-6 py-8">
                            {{ $sidebar }}
                        </div>
                    </aside>

                    {{-- Main Content --}}
                    <main class="min-w-0 flex-1 px-6 py-8 lg:px-8 xl:pr-0">
                        <div class="mx-auto max-w-2xl xl:mx-0 xl:max-w-none xl:pr-16">
                            {{ $slot }}
                        </div>
                    </main>

                    {{-- Right Sidebar: Page Headings --}}
                    <aside class="hidden xl:block xl:w-64 xl:shrink-0">
                        <div class="sticky top-14 h-[calc(100vh-3.5rem)] overflow-y-auto px-6 py-8">
                            {{ $aside }}
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </body>
</html>

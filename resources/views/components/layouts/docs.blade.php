<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>DeployerPHP {{ $title ?? 'Documentation' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white text-slate-900 dark:bg-slate-900 dark:text-slate-100">
        <div class="flex min-h-screen">
            {{-- Left Sidebar: Table of Contents --}}
            <aside class="w-64 shrink-0 border-r border-slate-200 dark:border-slate-700">
                <div class="sticky top-0 h-screen overflow-y-auto p-6">
                    <a href="{{ route('docs.index') }}" class="mb-8 block font-mono">
                        <span class="text-xl font-bold text-cyan-500">DeployerPHP</span>
                        <span class="flex text-xs tracking-tight">
                            <span class="text-cyan-500">━━━━</span>
                            <span class="text-blue-400">━━━━━</span>
                            <span class="text-fuchsia-500">━━━━━</span>
                            <span class="text-slate-400 dark:text-slate-500">━━━━━</span>
                        </span>
                    </a>
                    {{ $sidebar }}
                </div>
            </aside>

            {{-- Main Content --}}
            <main class="min-w-0 flex-1">
                <div class="mx-auto max-w-3xl px-8 py-12">
                    {{ $slot }}
                </div>
            </main>

            {{-- Right Sidebar: Page Headings --}}
            <aside class="hidden w-48 shrink-0 xl:block">
                <div class="sticky top-0 h-screen overflow-y-auto p-6">
                    {{ $aside }}
                </div>
            </aside>
        </div>
    </body>
</html>

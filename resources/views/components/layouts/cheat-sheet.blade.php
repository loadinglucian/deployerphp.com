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
        <x-docs.header />

        <flux:main class="p-0!">
            {{ $slot }}
        </flux:main>

        @fluxScripts
    </body>
</html>

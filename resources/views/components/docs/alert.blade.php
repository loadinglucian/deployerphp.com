@props([
    'type',
])

@php
    $normalizedType = match (strtolower((string) $type)) {
        'tip', 'note' => 'info',
        'warning' => 'important',
        default => strtolower((string) $type),
    };

    $styles = match ($normalizedType) {
        'info' => [
            'container' => 'border-l-cyan-400 dark:border-l-cyan-400',
            'icon' => 'text-cyan-500 dark:text-cyan-400',
        ],
        'important' => [
            'container' => 'border-l-amber-400 dark:border-l-amber-400',
            'icon' => 'text-amber-500 dark:text-amber-400',
        ],
        default => [
            'container' => 'border-l-zinc-400 dark:border-l-zinc-400',
            'icon' => 'text-zinc-500 dark:text-zinc-400',
        ],
    };
@endphp

<div class="{{ $styles['container'] }} flex flex-col gap-2 border-l-2 bg-zinc-100/50 px-3 py-2 dark:bg-zinc-800">
    <div class="{{ $styles['icon'] }}">
        @switch($normalizedType)
            @case('info')
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"
                    />
                </svg>

                @break
            @case('important')
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5" />
                </svg>

                @break
        @endswitch
    </div>

    <div class="min-w-0 grow [&>p:first-child]:mt-0! [&>p:last-child]:mb-0!">
        {!! $slot !!}
    </div>
</div>

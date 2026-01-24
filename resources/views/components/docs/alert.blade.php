@props(['type'])

@php
    $styles = match($type) {
        'tip' => [
            'container' => 'bg-cyan-50 border-cyan-400 text-cyan-800 dark:bg-cyan-900/50 dark:border-cyan-400 dark:text-cyan-200',
            'icon' => 'text-cyan-500 dark:text-cyan-400',
        ],
        'note' => [
            'container' => 'bg-blue-50 border-blue-400 text-blue-800 dark:bg-blue-900/50 dark:border-blue-400 dark:text-blue-200',
            'icon' => 'text-blue-500 dark:text-blue-400',
        ],
        'warning' => [
            'container' => 'bg-amber-50 border-amber-400 text-amber-800 dark:bg-amber-900/50 dark:border-amber-400 dark:text-amber-200',
            'icon' => 'text-amber-500 dark:text-amber-400',
        ],
        'important' => [
            'container' => 'bg-purple-50 border-purple-400 text-purple-800 dark:bg-purple-900/50 dark:border-purple-400 dark:text-purple-200',
            'icon' => 'text-purple-500 dark:text-purple-400',
        ],
        'caution' => [
            'container' => 'bg-red-50 border-red-400 text-red-800 dark:bg-red-900/50 dark:border-red-400 dark:text-red-200',
            'icon' => 'text-red-500 dark:text-red-400',
        ],
        default => [
            'container' => 'bg-zinc-50 border-zinc-400 text-zinc-800 dark:bg-zinc-900/50 dark:border-zinc-400 dark:text-zinc-200',
            'icon' => 'text-zinc-500 dark:text-zinc-400',
        ],
    };
@endphp

<div class="flex gap-3 rounded-lg border p-4 my-6 {{ $styles['container'] }}">
    <span class="shrink-0 mt-0.5 {{ $styles['icon'] }}">
        @switch($type)
            @case('tip')
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5"><path d="M8 1.5A5.5 5.5 0 0 0 2.5 7c0 1.58.67 3 1.74 4.01L4 15.5h8l-.24-4.49A5.5 5.5 0 0 0 8 1.5zM5.5 14.5v-1h5v1h-5z"/></svg>
                @break
            @case('note')
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5"><path fill-rule="evenodd" d="M8 1.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13zM7.25 8.5v-3h1.5v3h-1.5zm0 2.25a.75.75 0 1 1 1.5 0 .75.75 0 0 1-1.5 0z" clip-rule="evenodd"/></svg>
                @break
            @case('warning')
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5"><path fill-rule="evenodd" d="M8.893 1.5c-.183-.31-.52-.5-.887-.5s-.704.19-.887.5L.387 12.996c-.182.31-.182.69 0 1 .183.31.52.5.887.5h13.452c.367 0 .704-.19.887-.5.183-.31.183-.69 0-1L8.893 1.5zM7.25 5h1.5v4h-1.5V5zm.75 6.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5z" clip-rule="evenodd"/></svg>
                @break
            @case('important')
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5"><path fill-rule="evenodd" d="M8 1.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13zM7.25 4.75a.75.75 0 1 1 1.5 0 .75.75 0 0 1-1.5 0zm1.5 2.5v4h-1.5v-4h1.5z" clip-rule="evenodd"/></svg>
                @break
            @case('caution')
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5"><path fill-rule="evenodd" d="M8 1.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13zM6.22 6.22a.75.75 0 0 1 1.06 0L8 6.94l.72-.72a.75.75 0 1 1 1.06 1.06l-.72.72.72.72a.75.75 0 1 1-1.06 1.06L8 9.06l-.72.72a.75.75 0 1 1-1.06-1.06l.72-.72-.72-.72a.75.75 0 0 1 0-1.06z" clip-rule="evenodd"/></svg>
                @break
        @endswitch
    </span>
    <div class="min-w-0 grow [&>p:first-child]:mt-0 [&>p:last-child]:mb-0">
        {!! $slot !!}
    </div>
</div>

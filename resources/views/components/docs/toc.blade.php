@props([
    'toc',
    'currentPath',
])

<nav>
    <ul class="space-y-1">
        @foreach ($toc as $section)
            @foreach ($section['links'] as $link)
                @php
                    $isActive = $link['path'] === $currentPath;
                @endphp

                <li>
                    <a href="{{ route('docs.show', ['page' => $link['path']]) }}" @class([
                        'block px-3 py-1.5 text-sm transition-colors',
                        'border-l-2 border-accent text-zinc-900 dark:text-white' => $isActive,
                        'border-l border-zinc-200 text-zinc-500 hover:border-zinc-300 hover:text-zinc-900 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-white' => ! $isActive,
                    ])>
                        {{ $link['title'] }}
                    </a>
                </li>
            @endforeach
        @endforeach
    </ul>
</nav>
